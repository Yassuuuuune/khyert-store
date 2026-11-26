<?php

namespace Database\Seeders;

use App\Models\TourismCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeds the nine tourism categories used to classify destinations.
 * Keyed by slug so the seeder is fully idempotent.
 */
class TourismCategorySeeder extends Seeder
{
    /** @var array<int, array<string, mixed>> */
    private array $categories = [
        [
            'name' => 'Plages',
            'slug' => 'plages',
            'name_ar' => 'شواطئ',
            'name_en' => 'Beaches',
            'description' => 'Longues plages atlantiques et lagunes propices au surf et à la baignade.',
            'icon' => 'bi-water',
            'sort_order' => 1,
        ],
        [
            'name' => 'Déserts',
            'slug' => 'deserts',
            'name_ar' => 'صحاري',
            'name_en' => 'Deserts',
            'description' => 'Dunes, ergs et bivouacs sous les étoiles du Sahara.',
            'icon' => 'bi-sun',
            'sort_order' => 2,
        ],
        [
            'name' => 'Paysages naturels',
            'slug' => 'paysages-naturels',
            'name_ar' => 'مناظر طبيعية',
            'name_en' => 'Natural Landscapes',
            'description' => 'Oasis, canyons et réserves naturelles à couper le souffle.',
            'icon' => 'bi-tree',
            'sort_order' => 3,
        ],
        [
            'name' => 'Lieux historiques',
            'slug' => 'lieux-historiques',
            'name_ar' => 'أماكن تاريخية',
            'name_en' => 'Historical Places',
            'description' => 'Kasbahs, remparts et sites classés au patrimoine.',
            'icon' => 'bi-bank',
            'sort_order' => 4,
        ],
        [
            'name' => 'Expériences culturelles',
            'slug' => 'experiences-culturelles',
            'name_ar' => 'تجارب ثقافية',
            'name_en' => 'Cultural Experiences',
            'description' => 'Musique gnawa, festivals et rencontres avec les communautés locales.',
            'icon' => 'bi-people',
            'sort_order' => 5,
        ],
        [
            'name' => 'Marchés traditionnels',
            'slug' => 'marches-traditionnels',
            'name_ar' => 'أسواق تقليدية',
            'name_en' => 'Traditional Markets',
            'description' => 'Souks colorés où négocier épices, tissus et artisanat.',
            'icon' => 'bi-shop',
            'sort_order' => 6,
        ],
        [
            'name' => 'Hôtels et hébergements',
            'slug' => 'hotels-et-hebergements',
            'name_ar' => 'فنادق وإقامة',
            'name_en' => 'Hotels & Accommodations',
            'description' => 'Riads, maisons d\'hôtes et campements de charme.',
            'icon' => 'bi-building',
            'sort_order' => 7,
        ],
        [
            'name' => 'Restaurants',
            'slug' => 'restaurants',
            'name_ar' => 'مطاعم',
            'name_en' => 'Restaurants',
            'description' => 'Cuisine sahélienne et fruits de mer face à l\'océan.',
            'icon' => 'bi-cup-hot',
            'sort_order' => 8,
        ],
        [
            'name' => 'Activités et excursions',
            'slug' => 'activites-et-excursions',
            'name_ar' => 'أنشطة ورحلات',
            'name_en' => 'Activities & Excursions',
            'description' => 'Surf, kitesurf, quad, randonnées et sorties guidées.',
            'icon' => 'bi-compass',
            'sort_order' => 9,
        ],
    ];

    public function run(): void
    {
        DB::table('tourism_categories')->delete();

        foreach ($this->categories as $category) {
            TourismCategory::create($category + [
                'is_active' => true,
                'image' => null,
            ]);
        }
    }
}
