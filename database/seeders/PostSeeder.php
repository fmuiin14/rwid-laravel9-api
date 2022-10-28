<?php

namespace Database\Seeders;

use App\Models\Post;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PostSeeder extends Seeder
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
            Post::create([
                'image' => 'posts/link-to-image.jpg',
                'title' => 'Ini untuk judul ' . $i,
                'content' => 'ini konten ke ' . $i
            ]);
        }
    }
}
