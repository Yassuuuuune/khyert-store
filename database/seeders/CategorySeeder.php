<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeds the eight product categories of the marketplace.
 * Keyed by slug so the seeder is fully idempotent.
 */
class CategorySeeder extends Seeder
{
    /** @var array<int, array<string, mixed>> */
    private array $categories = [
        [
            'name' => 'Produits artisanaux traditionnels',
            'slug' => 'produits-artisanaux-traditionnels',
            'name_ar' => 'منتجات تقليدية',
            'name_en' => 'Traditional Moroccan Products',
            'description' => 'Tapis, cuivre, fer forgé et objets du patrimoine marocain façonnés à la main.',
            'icon' => 'bi-gem',
            'sort_order' => 1,
        ],
        [
            'name' => 'Vêtements et accessoires',
            'slug' => 'vetements-et-accessoires',
            'name_ar' => 'ملابس وإكسسوارات',
            'name_en' => 'Clothes & Accessories',
            'description' => 'Djellabas, caftans, babouches et châles cousus dans les règles de l\'art.',
            'icon' => 'bi-bag',
            'sort_order' => 2,
        ],
        [
            'name' => 'Produits faits main',
            'slug' => 'produits-faits-main',
            'name_ar' => 'منتجات يدوية',
            'name_en' => 'Handmade Products',
            'description' => 'Paniers, poteries, boiseries et bijoux réalisés par les artisans coopératifs.',
            'icon' => 'bi-hand-thumbs-up',
            'sort_order' => 3,
        ],
        [
            'name' => 'Cosmétiques et produits naturels',
            'slug' => 'cosmetiques-et-produits-naturels',
            'name_ar' => 'مستحضرات التجميل والمنتجات الطبيعية',
            'name_en' => 'Cosmetics & Natural Products',
            'description' => 'Huile d\'argan, savon noir, ghassoul et huile de cactus du terroir marocain.',
            'icon' => 'bi-droplet',
            'sort_order' => 4,
        ],
        [
            'name' => 'Produits alimentaires',
            'slug' => 'produits-alimentaires',
            'name_ar' => 'منتجات غذائية',
            'name_en' => 'Food Products',
            'description' => 'Dattes, miel, huile d\'olive et amlou issus des oasis du sud.',
            'icon' => 'bi-basket',
            'sort_order' => 5,
        ],
        [
            'name' => 'Électronique',
            'slug' => 'electronique',
            'name_ar' => 'إلكترونيات',
            'name_en' => 'Electronics',
            'description' => 'Accessoires électroniques et gadgets pratiques pour le voyage.',
            'icon' => 'bi-plug',
            'sort_order' => 6,
        ],
        [
            'name' => 'Décoration intérieure',
            'slug' => 'decoration-interieure',
            'name_ar' => 'ديكور المنزل',
            'name_en' => 'Home Decoration',
            'description' => 'Miroirs, lanternes, zellige et textiles pour habiller la maison.',
            'icon' => 'bi-house',
            'sort_order' => 7,
        ],
        [
            'name' => 'Autres produits locaux',
            'slug' => 'autres-produits-locaux',
            'name_ar' => 'منتجات محلية أخرى',
            'name_en' => 'Other Local Products',
            'description' => 'Encens, thés et curiosités régionales à découvrir.',
            'icon' => 'bi-box',
            'sort_order' => 8,
        ],
    ];

    public function run(): void
    {
        DB::table('categories')->delete();

        foreach ($this->categories as $category) {
            Category::create($category + [
                'is_active' => true,
                'image' => null,
            ]);
        }
    }
}
