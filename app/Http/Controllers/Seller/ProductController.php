<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductController extends Controller
{
    /** Settings key holding the platform product-moderation flag. */
    private const MODERATION_SETTING = 'product_moderation_enabled';

    /**
     * Paginated list of the authenticated seller's own products.
     */
    public function index(Request $request): View
    {
        $sellerId = $request->user()->id;

        $products = Product::where('seller_id', $sellerId)
            ->with('category')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->input('search');
                $query->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('name_ar', 'like', "%{$search}%")
                        ->orWhere('name_en', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->input('status'));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('seller.products.index', [
            'products' => $products,
            'filters'  => [
                'search' => $request->input('search'),
                'status' => $request->input('status'),
            ],
        ]);
    }

    /**
     * Show the product creation form.
     */
    public function create(): View
    {
        $this->authorize('create', Product::class);

        return view('seller.products.create', [
            'categories'  => $this->categories(),
            'moderation'  => $this->moderationEnabled(),
        ]);
    }

    /**
     * Persist a new product with its gallery.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Product::class);

        $validated = $this->validateProduct($request);

        DB::transaction(function () use ($request, $validated) {
            $validated['seller_id']         = $request->user()->id;
            $validated['status']            = $this->initialStatus();
            $validated['affiliate_enabled'] = $request->boolean('affiliate_enabled');

            $product = Product::create($validated);

            $this->storeImages($product, $request->file('images') ?? []);
        });

        return redirect()
            ->route('seller.products.index')
            ->with('success', __('seller.product_created'));
    }

    /**
     * Show the edit form for one of the seller's products.
     */
    public function edit(Product $product): View
    {
        $this->authorize('update', $product);

        $product->load('images');

        return view('seller.products.edit', [
            'product'    => $product,
            'categories' => $this->categories(),
            'moderation' => $this->moderationEnabled(),
        ]);
    }

    /**
     * Update an existing product and append new gallery images.
     */
    public function update(Request $request, Product $product): RedirectResponse
    {
        $this->authorize('update', $product);

        $validated = $this->validateProduct($request);

        DB::transaction(function () use ($request, $product, $validated) {
            $validated['affiliate_enabled'] = $request->boolean('affiliate_enabled');
            $validated['status']            = $this->resolveStatusForUpdate($product, $validated['status'] ?? null);

            $product->update($validated);

            $this->storeImages($product, $request->file('images') ?? []);
        });

        return redirect()
            ->route('seller.products.index')
            ->with('success', __('seller.product_updated'));
    }

    /**
     * Soft-delete a product together with its gallery files.
     */
    public function destroy(Product $product): RedirectResponse
    {
        $this->authorize('delete', $product);

        DB::transaction(function () use ($product) {
            foreach ($product->images as $image) {
                Storage::disk('public')->delete($image->path);
            }

            $product->images()->delete();
            $product->delete();
        });

        return redirect()
            ->route('seller.products.index')
            ->with('success', __('seller.product_deleted'));
    }

    /**
     * Delete a single gallery image belonging to one of the seller's products.
     */
    public function destroyImage(Product $product, ProductImage $image): RedirectResponse
    {
        $this->authorize('update', $product);

        abort_unless($image->product_id === $product->id, 404);

        $wasPrimary = $image->is_primary;

        Storage::disk('public')->delete($image->path);
        $image->delete();

        if ($wasPrimary) {
            $replacement = $product->images()->first();

            $product->update([
                'main_image' => $replacement?->path,
            ]);

            $replacement?->update(['is_primary' => true]);
        }

        return back()->with('success', __('seller.image_deleted'));
    }

    // ─── Internals ─────────────────────────────────────────────────────────────

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Category>
     */
    private function categories()
    {
        return Category::orderBy('name')->get();
    }

    /**
     * Validate and normalise the product payload.
     */
    private function validateProduct(Request $request): array
    {
        $validated = $request->validate([
            'name'                 => ['required', 'string', 'max:255'],
            'name_ar'              => ['nullable', 'string', 'max:255'],
            'name_en'              => ['nullable', 'string', 'max:255'],
            'description'          => ['required', 'string'],
            'description_ar'       => ['nullable', 'string'],
            'description_en'       => ['nullable', 'string'],
            'category_id'          => ['required', 'exists:categories,id'],
            'price'                => ['required', 'numeric', 'min:0'],
            'sale_price'           => ['nullable', 'numeric', 'min:0', 'lt:price'],
            'cost_price'           => ['nullable', 'numeric', 'min:0'],
            'stock'                => ['required', 'integer', 'min:0'],
            'sku'                  => ['nullable', 'string', 'max:255'],
            'weight'               => ['nullable', 'numeric', 'min:0'],
            'affiliate_commission' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'affiliate_enabled'    => ['nullable', 'boolean'],
            'status'               => ['nullable', 'string', 'in:draft,active,archived'],
            'images'               => ['nullable', 'array'],
            'images.*'             => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        // The uploaded files are handled separately; keep them out of mass assignment.
        unset($validated['images']);

        return $validated;
    }

    /**
     * Persist any uploaded gallery images and wire up the primary one.
     *
     * @param  array<int, \Illuminate\Http\UploadedFile>  $files
     */
    private function storeImages(Product $product, array $files): void
    {
        $files = array_filter($files, fn ($file) => $file && $file->isValid());

        if (empty($files)) {
            return;
        }

        $sortOrder = (int) $product->images()->max('sort_order');
        $hasPrimary = $product->images()->where('is_primary', true)->exists();

        foreach ($files as $file) {
            $path = $file->store('products/' . $product->seller_id, 'public');
            $sortOrder++;

            $isPrimary = ! $hasPrimary;
            $hasPrimary = true;

            $product->images()->create([
                'path'       => $path,
                'alt'        => $product->name,
                'sort_order' => $sortOrder,
                'is_primary' => $isPrimary,
            ]);

            if ($isPrimary) {
                $product->update(['main_image' => $path]);
            }
        }
    }

    /** Whether platform-wide product moderation is currently enabled. */
    private function moderationEnabled(): bool
    {
        return (bool) Setting::get(self::MODERATION_SETTING, true);
    }

    /** Status a brand-new product starts in. */
    private function initialStatus(): string
    {
        return $this->moderationEnabled() ? 'pending' : 'active';
    }

    /**
     * Decide the persisted status on update without letting a seller bypass
     * moderation by flipping a not-yet-approved product straight to active.
     */
    private function resolveStatusForUpdate(Product $product, ?string $requested): string
    {
        $requested = $requested ?: $product->status;

        if ($this->moderationEnabled() && $product->status !== 'active' && $requested === 'active') {
            return 'pending';
        }

        return $requested;
    }
}
