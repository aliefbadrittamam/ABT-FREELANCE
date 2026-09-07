<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Major;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categoryData = [
            'Joki Tugas' => ['Skripsi', 'Jurnal', 'PPT / Presentasi', 'Paper / Makalah', 'Olah Data / SPSS', 'Essai / Artikel'],
            'Jasa Website' => ['Website Landing Page', 'Web Application / Fullstack', 'Custom Script / Bugfix', 'Portfolio Web'],
            'Desain Grafis' => ['Logo & Brand Identity', 'Banner / Poster / Social Media', 'UI/UX App Design'],
        ];

        foreach ($categoryData as $catName => $subCats) {
            $category = Category::firstOrCreate(['name' => $catName]);
            foreach ($subCats as $subName) {
                SubCategory::firstOrCreate([
                    'category_id' => $category->id,
                    'name' => $subName,
                ]);
            }
        }

        $majors = [
            'Informatika / Ilmu Komputer',
            'Sistem Informasi',
            'Manajemen',
            'Akuntansi',
            'Hukum',
            'Teknik Sipil',
            'Ilmu Komunikasi',
            'Psikologi',
            'Teknik Elektro',
            'Kedokteran / Farmasi',
        ];

        foreach ($majors as $mName) {
            Major::firstOrCreate(['name' => $mName]);
        }
    }
}
