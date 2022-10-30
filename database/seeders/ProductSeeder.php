<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        for($i = 0; $i < 10000; $i ++)
        {
            Product::create([
                'image' => 'posts/link-to-image.jpg',
                'title' => 'Ini untuk judul ' . $i,
                'description' => 'ini deskripsi ke ' . $i
            ]);
        }
    }
}
