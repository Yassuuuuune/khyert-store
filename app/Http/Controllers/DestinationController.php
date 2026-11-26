<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Models\TourismCategory;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Public-facing tourism destination controller for SAHARA CONNECT.
 *
 * Supports filtering by TourismCategory slug, keyword search,
 * and provides a Leaflet.js-compatible map view with GeoJSON data.
 */
class DestinationController extends Controller
{
    /**
     * Display a paginated, filtered list of active destinations.
     *
     * Filters:
     *  - `category` (slug)  → limit to destinations tagged with that tourism category
     *  - `q`                → keyword search across name fields and city
     */
    public function index(Request $request): Renderable
    {
        $query = Destination::active()
            ->with('tourismCategories');

        // ── Filter: tourism category slug ────────────────────────────────────────
        if ($request->filled('category')) {
            $query->whereHas('tourismCategories', function (Builder $q) use ($request) {
                $q->where('slug', $request->input('category'));
            });
        }

        // ── Filter: keyword search ───────────────────────────────────────────────
        if ($request->filled('q')) {
            $term = '%' . $request->input('q') . '%';
            $query->where(function (Builder $q) use ($term) {
                $q->where('name', 'like', $term)
                  ->orWhere('name_ar', 'like', $term)
                  ->orWhere('name_en', 'like', $term)
                  ->orWhere('city', 'like', $term)
                  ->orWhere('region', 'like', $term)
                  ->orWhere('description', 'like', $term);
            });
        }

        $destinations = $query->orderByDesc('is_featured')
            ->orderByDesc('views')
            ->paginate(12)
            ->withQueryString();

        // Sidebar: all active tourism categories
        $tourismCategories = TourismCategory::active()
            ->orderBy('sort_order')
            ->withCount(['destinations' => fn (Builder $q) => $q->active()])
            ->get();

        // Resolve active tourism category for sidebar highlighting
        $activeCategory = null;
        if ($request->filled('category')) {
            $activeCategory = TourismCategory::where('slug', $request->input('category'))->first();
        }

        return view('tourism.index', compact(
            'destinations',
            'tourismCategories',
            'activeCategory'
        ));
    }

    /**
     * Display a single destination's detail page.
     *
     * Increments the view counter and loads images, tourism categories,
     * and up to 4 related destinations sharing at least one category.
     */
    public function show(Destination $destination): Renderable
    {
        abort_unless($destination->is_active, 404);

        // Increment view counter
        $destination->increment('views');

        $destination->load(['images', 'tourismCategories']);

        // Related destinations: share at least one tourism category
        $categoryIds = $destination->tourismCategories->pluck('id');

        $relatedDestinations = Destination::active()
            ->where('id', '!=', $destination->id)
            ->whereHas('tourismCategories', function (Builder $q) use ($categoryIds) {
                $q->whereIn('tourism_categories.id', $categoryIds);
            })
            ->with(['tourismCategories'])
            ->take(4)
            ->get();

        return view('tourism.show', compact('destination', 'relatedDestinations'));
    }

    /**
     * Return the interactive map view with all active destinations as JSON.
     *
     * The view receives a GeoJSON-compatible array that can be consumed
     * directly by Leaflet.js to render markers.
     */
    public function map(): Renderable
    {
        $destinations = Destination::active()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->with('tourismCategories')
            ->get();

        // Build a lightweight GeoJSON FeatureCollection for the front-end map
        $geoJson = [
            'type'     => 'FeatureCollection',
            'features' => $destinations->map(function (Destination $d) {
                return [
                    'type'       => 'Feature',
                    'geometry'   => [
                        'type'        => 'Point',
                        'coordinates' => [$d->longitude, $d->latitude],
                    ],
                    'properties' => [
                        'id'         => $d->id,
                        'name'       => $d->localized_name,
                        'slug'       => $d->slug,
                        'city'       => $d->city,
                        'region'     => $d->region,
                        'image'      => $d->main_image_url,
                        'categories' => $d->tourismCategories->pluck('name'),
                        'url'        => route('tourism.show', $d->slug),
                    ],
                ];
            })->values()->all(),
        ];

        return view('tourism.map', [
            'destinations' => $destinations,
            'geoJson'      => json_encode($geoJson, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
        ]);
    }
}
