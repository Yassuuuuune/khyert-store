<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

/**
 * Seeds the platform-wide settings managed from the admin back-office.
 *
 * Idempotent: every row is keyed by its unique `key`, so re-running the
 * seeder simply refreshes the values.
 */
class SettingSeeder extends Seeder
{
    public function run(): void
    {
        /** @var array<string, array{0: string, 1: string, 2: string}> $settings */
        $settings = [
            // key                       value                                                          group       type
            'site_name'                 => ['SAHARA CONNECT', 'general', 'text'],
            'site_tagline'              => ['Discover the South. Shop Local. Experience More.', 'general', 'text'],
            'site_description'          => ['Place de marché, programme d\'affiliation et plateforme touristique dédiés au sud marocain.', 'general', 'text'],
            'contact_email'             => ['contact@sahara-connect.ma', 'contact', 'text'],
            'contact_phone'             => ['+212 528 87 71 23', 'contact', 'text'],
            'contact_whatsapp'          => ['+212 6 61 23 45 67', 'contact', 'text'],
            'address'                   => ['Avenue Mohammed V, Guelmim 81000, Maroc', 'contact', 'text'],
            'social_facebook'           => ['https://facebook.com/saharaconnect', 'social', 'text'],
            'social_instagram'          => ['https://instagram.com/saharaconnect', 'social', 'text'],
            'social_twitter'            => ['https://twitter.com/saharaconnect', 'social', 'text'],
            'social_youtube'            => ['https://youtube.com/@saharaconnect', 'social', 'text'],
            'currency'                  => ['MAD', 'shop', 'text'],
            'currency_symbol'           => ['DH', 'shop', 'text'],
            'shipping_fee'              => ['40', 'shop', 'number'],
            'free_shipping_threshold'   => ['500', 'shop', 'number'],
            'default_commission_rate'   => ['10', 'affiliate', 'number'],
            'min_payout'                => ['100', 'affiliate', 'number'],
            'products_per_page'         => ['12', 'shop', 'number'],
            'maintenance_mode'          => ['0', 'general', 'boolean'],
        ];

        foreach ($settings as $key => [$value, $group, $type]) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'group' => $group, 'type' => $type],
            );
        }
    }
}
