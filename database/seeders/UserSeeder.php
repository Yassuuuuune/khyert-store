<?php

namespace Database\Seeders;

use App\Models\AffiliateProfile;
use App\Models\SellerProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

/**
 * Seeds the demo accounts for every role, plus the seller / affiliate profiles
 * needed to exercise the admin moderation workflow.
 *
 * Demo credentials (all use the password "password"):
 *   admin@sahara-connect.ma     -> admin
 *   vendeur@sahara-connect.ma   -> seller  (approved)
 *   fatima@sahara-connect.ma    -> seller  (approved, second shop)
 *   affilie@sahara-connect.ma   -> affiliate (approved)
 *   client@sahara-connect.ma    -> customer
 *   artisan@sahara-connect.ma   -> seller  (pending approval)
 *   promoteur@sahara-connect.ma -> affiliate (pending approval)
 */
class UserSeeder extends Seeder
{
    /** @var array<int, array<string, mixed>> */
    private array $customers = [
        ['name' => 'Karim Ouhadi',      'email' => 'client@sahara-connect.ma'],
        ['name' => 'Salma Bennani',     'email' => 'salma.bennani@example.ma'],
        ['name' => 'Youssef El Idrissi', 'email' => 'youssef.elidrissi@example.ma'],
        ['name' => 'Nadia Chraibi',     'email' => 'nadia.chraibi@example.ma'],
        ['name' => 'Omar Tazi',         'email' => 'omar.tazi@example.ma'],
        ['name' => 'Fatima Zahra Alaoui', 'email' => 'fatimazahra.alaoui@example.ma'],
        ['name' => 'Mehdi Bouazza',     'email' => 'mehdi.bouazza@example.ma'],
        ['name' => 'Khadija Naciri',    'email' => 'khadija.naciri@example.ma'],
        ['name' => 'Rachid Benjelloun', 'email' => 'rachid.benjelloun@example.ma'],
    ];

    public function run(): void
    {
        // Detach user rows from any leftover references before re-seeding.
        Schema::withoutForeignKeyConstraints(function () {
            DB::table('affiliate_profiles')->delete();
            DB::table('seller_profiles')->delete();
            DB::table('users')->delete();
        });

        // ── Administrator ──────────────────────────────────────────────────────
        $this->makeUser([
            'name' => 'Administrateur Sahara',
            'email' => 'admin@sahara-connect.ma',
            'role' => 'admin',
            'status' => 'active',
            'locale' => 'fr',
            'phone' => '+212 528 87 71 00',
        ]);

        // ── Approved sellers ──────────────────────────────────────────────────
        $hassan = $this->makeUser([
            'name' => 'Hassan El Amrani',
            'email' => 'vendeur@sahara-connect.ma',
            'role' => 'seller',
            'status' => 'active',
            'locale' => 'fr',
            'phone' => '+212 661 20 30 40',
        ]);

        $fatima = $this->makeUser([
            'name' => 'Fatima Tissir',
            'email' => 'fatima@sahara-connect.ma',
            'role' => 'seller',
            'status' => 'active',
            'locale' => 'fr',
            'phone' => '+212 662 41 52 63',
        ]);

        SellerProfile::updateOrCreate(
            ['user_id' => $hassan->id],
            [
                'shop_name' => 'Artisanat Hassan El Amrani',
                'slug' => 'artisanat-hassan-el-amrani',
                'description' => 'Ébéniste et dinandier à Guelmim depuis plus de vingt ans. Tapis, cuivre et fer forgé façonnés à la main selon les traditions du sud marocain.',
                'city' => 'Guelmim',
                'region' => 'Guelmim-Oued Noun',
                'phone' => '+212 661 20 30 40',
                'whatsapp' => '+212 661 20 30 40',
                'commission_rate' => 10.00,
                'status' => 'approved',
            ],
        );

        SellerProfile::updateOrCreate(
            ['user_id' => $fatima->id],
            [
                'shop_name' => 'Maison de l\'Artisanat Sahraoui',
                'slug' => 'maison-de-l-artisanat-sahraoui',
                'description' => 'Coopérative féminine de Dakhla spécialisée dans les cosmétiques naturels, la décoration et les produits alimentaires du Sahara.',
                'city' => 'Dakhla',
                'region' => 'Dakhla-Oued Ed-Dahab',
                'phone' => '+212 662 41 52 63',
                'whatsapp' => '+212 662 41 52 63',
                'commission_rate' => 12.00,
                'status' => 'approved',
            ],
        );

        // ── Seller awaiting admin approval ────────────────────────────────────
        $artisan = $this->makeUser([
            'name' => 'Brahim Oubella',
            'email' => 'artisan@sahara-connect.ma',
            'role' => 'seller',
            'status' => 'active',
            'locale' => 'fr',
            'phone' => '+212 663 74 85 96',
        ]);

        SellerProfile::updateOrCreate(
            ['user_id' => $artisan->id],
            [
                'shop_name' => 'Coopérative Artisanale Sahara',
                'slug' => 'cooperative-artisanale-sahara',
                'description' => 'Jeune coopérative de Tan-Tan proposant des paniers en palmier, poteries et bijoux touaregs. En attente de validation par l\'administration.',
                'city' => 'Tan-Tan',
                'region' => 'Guelmim-Oued Noun',
                'phone' => '+212 663 74 85 96',
                'whatsapp' => '+212 663 74 85 96',
                'commission_rate' => 10.00,
                'status' => 'pending',
            ],
        );

        // ── Approved affiliate ────────────────────────────────────────────────
        $yasmine = $this->makeUser([
            'name' => 'Yasmine Benali',
            'email' => 'affilie@sahara-connect.ma',
            'role' => 'affiliate',
            'status' => 'active',
            'locale' => 'fr',
            'phone' => '+212 664 96 07 18',
        ]);

        AffiliateProfile::updateOrCreate(
            ['user_id' => $yasmine->id],
            [
                'website' => 'https://yasmine-benali.ma',
                'promotion_methods' => 'Blog voyage, Instagram, TikTok et newsletter (plus de 45 000 abonnés).',
                'payment_method' => 'bank',
                'payment_details' => 'MA64 0112 3456 7890 1234 5678',
                'status' => 'approved',
            ],
        );

        // ── Affiliate awaiting admin approval ─────────────────────────────────
        $promoteur = $this->makeUser([
            'name' => 'Anas Loukili',
            'email' => 'promoteur@sahara-connect.ma',
            'role' => 'affiliate',
            'status' => 'active',
            'locale' => 'fr',
            'phone' => '+212 665 18 29 30',
        ]);

        AffiliateProfile::updateOrCreate(
            ['user_id' => $promoteur->id],
            [
                'website' => 'https://instagram.com/anas.sahara',
                'promotion_methods' => 'Instagram, TikTok et bouche à oreille dans la région de Laayoune.',
                'payment_method' => 'paypal',
                'payment_details' => 'promoteur@sahara-connect.ma',
                'status' => 'pending',
            ],
        );

        // ── Customers ─────────────────────────────────────────────────────────
        foreach ($this->customers as $customer) {
            $this->makeUser([
                'name' => $customer['name'],
                'email' => $customer['email'],
                'role' => 'customer',
                'status' => 'active',
                'locale' => 'fr',
                'phone' => $this->phone(),
            ]);
        }
    }

    /**
     * Create or refresh a demo user, always keeping a unique referral code.
     *
     * @param  array<string, mixed>  $attributes
     */
    private function makeUser(array $attributes): User
    {
        $attributes['password'] = Hash::make('password');
        $attributes['email_verified_at'] = now();

        /** @var User|null $user */
        $user = User::withTrashed()->where('email', $attributes['email'])->first();

        if ($user) {
            $user->fill($attributes);
            $user->deleted_at = null;

            if (! $user->referral_code) {
                $user->referral_code = User::generateReferralCode();
            }

            $user->save();

            return $user;
        }

        return User::create($attributes + [
            'referral_code' => User::generateReferralCode(),
        ]);
    }

    private function phone(): string
    {
        return '+212 6' . random_int(10, 79) . ' ' . random_int(10, 99)
            . ' ' . random_int(10, 99) . ' ' . random_int(10, 99);
    }
}
