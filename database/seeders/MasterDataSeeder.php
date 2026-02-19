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
        Client::create([
            'code' => 'CL003',
            'name' => 'Nasgor Gondrong CGE',
            'address' => 'Jl. Raya Tapos, Tapos, Kec. Tapos, Kota Depok, Jawa Barat 16457',
            'email' => null,
            'phone' => '085820008082',
            'pic_name' => 'Henry Hermawan',
            'payment_terms' => 7, // NET 7
            'is_active' => true,
        ]);
    }
}
