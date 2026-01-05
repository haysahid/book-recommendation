<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // Site Information - Group: 'general'
            [
                'key' => 'site.name',
                'value' => 'Book Reco',
                'type' => 'text',
                'name' => 'Site Name',
                'description' => 'The name of the application displayed in the header and title.',
                'group' => 'general',
            ],
            [
                'key' => 'site.description',
                'value' => 'A smart book recommendation system.',
                'type' => 'text',
                'name' => 'Site Description',
                'description' => 'A brief description of the application.',
                'group' => 'general',
            ],
            [
                'key' => 'site.logo',
                'value' => 'bookreco_logo.png',
                'type' => 'image',
                'name' => 'Site Logo',
                'description' => 'The logo image displayed in the application header.',
                'group' => 'general',
            ],
            [
                'key' => 'site.logo_white',
                'value' => 'bookreco_logo_white.png',
                'type' => 'image',
                'name' => 'Site Logo White',
                'description' => 'The white version of the logo image displayed in the application header.',
                'group' => 'general',
            ],
            [
                'key' => 'site.logo_black',
                'value' => 'bookreco_logo_black.png',
                'type' => 'image',
                'name' => 'Site Logo Black',
                'description' => 'The black version of the logo image displayed in the application header.',
                'group' => 'general',
            ],
            [
                'key' => 'site.favicon',
                'value' => 'bookreco_favicon.png',
                'type' => 'image',
                'name' => 'Site Favicon',
                'description' => 'The favicon image displayed in the browser tab.',
                'group' => 'general',
            ],
            [
                'key' => 'site.banner',
                'value' => 'bookreco_banner.jpg',
                'type' => 'image',
                'name' => 'Site Banner',
                'description' => 'The banner image displayed on the homepage.',
                'group' => 'general',
            ],

            // Company Information - Group: 'general'
            [
                'key' => 'company.name',
                'value' => 'Book Reco Team',
                'type' => 'text',
                'name' => 'Company Name',
                'description' => 'The official name of the team or organization.',
                'group' => 'general',
            ],
            [
                'key' => 'company.logo',
                'value' => 'bookreco_team_logo.png',
                'type' => 'image',
                'name' => 'Company Logo',
                'description' => 'The logo image displayed in the company profile.',
                'group' => 'general',
            ],
            [
                'key' => 'company.description',
                'value' => 'A passionate team building book recommendation solutions.',
                'type' => 'textarea',
                'name' => 'Company Description',
                'description' => 'A brief description of the team or organization.',
                'group' => 'general',
            ],
            [
                'key' => 'company.address',
                'value' => 'Jl. AMIKOM, Yogyakarta, Indonesia',
                'type' => 'text',
                'name' => 'Company Address',
                'description' => 'The physical address of the team or organization.',
                'group' => 'general',
            ],
            [
                'key' => 'company.phone',
                'value' => '081234567890',
                'type' => 'text',
                'name' => 'Company Phone',
                'description' => 'The contact phone number for the team or organization.',
                'group' => 'general',
            ],
            [
                'key' => 'company.email',
                'value' => 'info@bookreco.com',
                'type' => 'email',
                'name' => 'Company Email',
                'description' => 'The email address for the team or organization.',
                'group' => 'general',
            ],
            [
                'key' => 'company.social_links',
                'value' => json_encode([
                    'facebook' => null,
                    'instagram' => null,
                    'tiktok' => null,
                ]),
                'type' => 'json',
                'name' => 'Social Links',
                'description' => 'Links to the site\'s social media profiles.',
                'group' => 'general',
            ],

            // Utilities - Group: 'general'
            [
                'key' => 'app.maintenance_mode',
                'value' => '0',
                'type' => 'boolean',
                'name' => 'Maintenance Mode',
                'description' => 'Enable or disable maintenance mode for the application.',
                'group' => 'general',
            ],
        ];

        foreach ($settings as $setting) {
            Setting::firstOrCreate(
                ['key' => $setting['key']],
                [
                    'value' => $setting['value'],
                    'type' => $setting['type'],
                    'name' => $setting['name'],
                    'description' => $setting['description'],
                    'group' => $setting['group'],
                ]
            );
        }
    }
}
