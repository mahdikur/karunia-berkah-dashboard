<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Item;
use App\Models\Client;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Categories
        $cat1 = Category::create(['name' => 'Sayur', 'is_active' => true]);
        $cat2 = Category::create(['name' => 'Buah', 'is_active' => true]);
        $cat3 = Category::create(['name' => 'Daging', 'is_active' => true]);
        $cat4 = Category::create(['name' => 'Ikan', 'is_active' => true]);
        $cat5 = Category::create(['name' => 'Bumbu', 'is_active' => true]);
        $cat6 = Category::create(['name' => 'Lainnya', 'is_active' => true]);

        // Items
        Item::create([
            'category_id' => $cat1->id,
            'code' => 'ITM001',
            'name' => 'Bayam',
            'unit' => 'ikat',
            'is_active' => true,
        ]);
        Item::create([
            'category_id' => $cat1->id,
            'code' => 'ITM002',
            'name' => 'Kangkung',
            'unit' => 'ikat',
            'is_active' => true,
        ]);
        Item::create([
            'category_id' => $cat2->id,
            'code' => 'ITM003',
            'name' => 'Apel',
            'unit' => 'kg',
            'is_active' => true,
        ]);
        Item::create([
            'category_id' => $cat3->id,
            'code' => 'ITM004',
            'name' => 'Daging Sapi',
            'unit' => 'kg',
            'is_active' => true,
        ]);
        Item::create([
            'category_id' => $cat4->id,
            'code' => 'ITM005',
            'name' => 'Ikan Salmon',
            'unit' => 'kg',
            'is_active' => true,
        ]);
        Item::create([
            'category_id' => $cat5->id,
            'code' => 'ITM006',
            'name' => 'Bawang Merah',
            'unit' => 'kg',
            'is_active' => true,
        ]);
        Item::create([
            'category_id' => $cat6->id,
            'code' => 'ITM007',
            'name' => 'Air Mineral',
            'unit' => 'ltr',
            'is_active' => true,
        ]);

        // Clients
        Client::create([
            'code' => 'CL001',
            'name' => 'Selera Pasundan',
            'address' => 'Jl. Raya Tapos, Tapos, Kec. Tapos, Kota Depok, Jawa Barat 16457',
            'email' => null,
            'phone' => '085820008082',
            'pic_name' => 'Henry Hermawan',
            'payment_terms' => 30, // NET 30
            'is_active' => true,
        ]);
        Client::create([
            'code' => 'CL002',
            'name' => 'NUKA MARI KOPI',
            'address' => 'Jl. Raya Mayor Oking Jaya Atmaja No.36c, Ciriung, Kec. Cibinong, Kabupaten Bogor',
            'email' => null,
            'phone' => '08992070899',
            'pic_name' => 'Jamaludin Al Muhtari',
            'payment_terms' => 0, // COD / Cash & Carry
            'is_active' => true,
        ]);
    }
}
