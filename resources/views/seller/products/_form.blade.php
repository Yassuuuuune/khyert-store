@php
    /** @var \App\Models\Product|null $product */
    $product = $product ?? null;
    $routeParams = $product ? [$product] : [];
    $method = $product ? 'PUT' : 'POST';
    $submitLabel = $product ? __('seller.update_product') : __('seller.save_product');
    $selectableStatuses = ['draft', 'active', 'archived'];
@endphp

@if ($moderation)
    <div class="alert alert-info d-flex align-items-center">
        <i class="bi bi-info-circle-fill me-2"></i>
        <div>{{ __('seller.moderation_notice') }}</div>
    </div>
@endif

<form action="{{ $action }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method($method)

    <div class="row g-3">
        {{-- ── Product information ─────────────────────────────────────────── --}}
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-header bg-white">
                    <h2 class="h6 mb-0 text-brown">{{ __('seller.product_info') }}</h2>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('seller.product_name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="name" value="{{ old('name', $product->name ?? '') }}"
                                   class="form-control @error('name') is-invalid @enderror" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('seller.category') }} <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                                <option value="">{{ __('seller.choose_category') }}</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}"
                                        @selected((int) old('category_id', $product->category_id ?? null) === $category->id)>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">{{ __('seller.product_name_ar') }}</label>
                            <input type="text" name="name_ar" value="{{ old('name_ar', $product->name_ar ?? '') }}"
                                   class="form-control @error('name_ar') is-invalid @enderror" dir="rtl">
                            @error('name_ar')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('seller.product_name_en') }}</label>
                            <input type="text" name="name_en" value="{{ old('name_en', $product->name_en ?? '') }}"
                                   class="form-control @error('name_en') is-invalid @enderror">
                            @error('name_en')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label">{{ __('seller.description') }} <span class="text-danger">*</span></label>
                            <textarea name="description" rows="3"
                                      class="form-control @error('description') is-invalid @enderror" required>{{ old('description', $product->description ?? '') }}</textarea>
                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('seller.description_ar') }}</label>
                            <textarea name="description_ar" rows="3" dir="rtl"
                                      class="form-control @error('description_ar') is-invalid @enderror">{{ old('description_ar', $product->description_ar ?? '') }}</textarea>
                            @error('description_ar')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('seller.description_en') }}</label>
                            <textarea name="description_en" rows="3"
                                      class="form-control @error('description_en') is-invalid @enderror">{{ old('description_en', $product->description_en ?? '') }}</textarea>
                            @error('description_en')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Gallery ─────────────────────────────────────────────────── --}}
            <div class="card mb-3">
                <div class="card-header bg-white">
                    <h2 class="h6 mb-0 text-brown">{{ __('seller.gallery') }}</h2>
                </div>
                <div class="card-body">
                    @if ($product && $product->images->isNotEmpty())
                        <label class="form-label">{{ __('seller.current_images') }}</label>
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            @foreach ($product->images as $image)
                                <div class="position-relative">
                                    <img src="{{ $image->url }}" alt="{{ $image->alt }}"
                                         class="rounded" style="width:88px;height:88px;object-fit:cover;">
                                    @if ($image->is_primary)
                                        <span class="badge badge-featured position-absolute top-0 start-0 m-1">
                                            {{ __('seller.main_image') }}
                                        </span>
                                    @endif
                                    <button type="submit"
                                            form="delete-image-{{ $image->id }}"
                                            class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 rounded-circle p-0"
                                            style="width:24px;height:24px;"
                                            onclick="return confirm('{{ __('seller.delete_image_confirm') }}');"
                                            title="{{ __('messages.delete') }}">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <label class="form-label">{{ __('seller.add_images') }}</label>
                    <input type="file" name="images[]" multiple accept="image/*"
                           class="form-control @error('images.*') is-invalid @enderror">
                    <div class="form-text">{{ __('seller.gallery_hint') }}</div>
                    @error('images.*')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        {{-- ── Sidebar: pricing, stock, affiliate, publication ─────────────── --}}
        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-header bg-white">
                    <h2 class="h6 mb-0 text-brown">{{ __('seller.pricing_stock') }}</h2>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __('seller.price') }} <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0" name="price"
                               value="{{ old('price', $product->price ?? '') }}"
                               class="form-control @error('price') is-invalid @enderror" required>
                        @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('seller.sale_price') }}</label>
                        <input type="number" step="0.01" min="0" name="sale_price"
                               value="{{ old('sale_price', $product->sale_price ?? '') }}"
                               class="form-control @error('sale_price') is-invalid @enderror">
                        @error('sale_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('seller.cost_price') }}</label>
                        <input type="number" step="0.01" min="0" name="cost_price"
                               value="{{ old('cost_price', $product->cost_price ?? '') }}"
                               class="form-control @error('cost_price') is-invalid @enderror">
                        @error('cost_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('seller.stock') }} <span class="text-danger">*</span></label>
                        <input type="number" step="1" min="0" name="stock"
                               value="{{ old('stock', $product->stock ?? 0) }}"
                               class="form-control @error('stock') is-invalid @enderror" required>
                        @error('stock')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('seller.sku') }}</label>
                        <input type="text" name="sku" value="{{ old('sku', $product->sku ?? '') }}"
                               class="form-control @error('sku') is-invalid @enderror">
                        @error('sku')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label class="form-label">{{ __('seller.weight') }}</label>
                        <input type="number" step="0.001" min="0" name="weight"
                               value="{{ old('weight', $product->weight ?? '') }}"
                               class="form-control @error('weight') is-invalid @enderror">
                        @error('weight')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header bg-white">
                    <h2 class="h6 mb-0 text-brown">{{ __('seller.affiliate_section') }}</h2>
                </div>
                <div class="card-body">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" role="switch"
                               id="affiliate_enabled" name="affiliate_enabled" value="1"
                               @checked(old('affiliate_enabled', $product->affiliate_enabled ?? true))>
                        <label class="form-check-label" for="affiliate_enabled">{{ __('seller.affiliate_enabled') }}</label>
                    </div>
                    <div>
                        <label class="form-label">{{ __('seller.commission') }}</label>
                        <input type="number" step="0.01" min="0" max="100" name="affiliate_commission"
                               value="{{ old('affiliate_commission', $product->affiliate_commission ?? '') }}"
                               class="form-control @error('affiliate_commission') is-invalid @enderror">
                        <div class="form-text">{{ __('seller.commission_hint') }}</div>
                        @error('affiliate_commission')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header bg-white">
                    <h2 class="h6 mb-0 text-brown">{{ __('seller.publication') }}</h2>
                </div>
                <div class="card-body">
                    <label class="form-label">{{ __('seller.status') }}</label>
                    <select name="status" class="form-select @error('status') is-invalid @enderror">
                        @foreach ($selectableStatuses as $status)
                            <option value="{{ $status }}"
                                @selected(old('status', $product->status ?? 'draft') === $status)>
                                {{ __('status.' . $status) }}
                            </option>
                        @endforeach
                    </select>
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">
                    <i class="bi bi-check-lg me-1"></i>{{ $submitLabel }}
                </button>
                <a href="{{ route('seller.products.index') }}" class="btn btn-outline-secondary">
                    {{ __('messages.cancel') }}
                </a>
            </div>
        </div>
    </div>

    @if ($product && $product->images->isNotEmpty())
        @foreach ($product->images as $image)
            <form id="delete-image-{{ $image->id }}"
                  action="{{ route('seller.products.destroy-image', [$product, $image]) }}"
                  method="POST" class="d-none">
                @csrf
                @method('DELETE')
            </form>
        @endforeach
    @endif
</form>
