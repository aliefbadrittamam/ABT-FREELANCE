<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = ['Joki Tugas', 'Jasa Website', 'Desain Grafis'];
        foreach ($categories as $name) {
            Category::firstOrCreate(['name' => $name]);
        }
    }
}
