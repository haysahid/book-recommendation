<?php

namespace Database\Seeders;

use App\Models\Store;
use App\Models\StoreAdvantage;
use App\Models\StoreCertificate;
use App\Models\UserStoreRole;
use App\Models\StoreSocialLink;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Store::updateOrCreate(
            ['email' => 'bookreco@example.com'],
            [
                'name' => 'Book Reco',
                'description' => 'Book Reco is a book recommendation store that provides a variety of books from different genres for all audiences.',
                'address' => 'Jl. Buku Raya, Literasi, Kec. Pengetahuan, Kabupaten Edukasi, Provinsi Cerdas',
                'phone' => '021-98765432',
                'logo' => 'book_reco_logo.png',
                'banner' => 'book_reco_banner.png',
                'rajaongkir_origin_id' => 73442,
                'rajaongkir_origin_label' => 'KUTA BUMI, PASAR KEMIS, TANGERANG, BANTEN, 15560',
                'province_name' => 'BANTEN',
                'city_name' => 'TANGERANG',
                'district_name' => 'PASAR KEMIS',
                'subdistrict_name' => 'KUTABUMI',
                'zip_code' => '15560',
            ]
        );
    }
}
