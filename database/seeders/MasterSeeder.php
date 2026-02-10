<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Uom;
use App\Models\EnquiryType;
use App\Models\Source;
use App\Models\Company;
use App\Models\Supplier;
use App\Models\Project;
use App\Models\Product;
use App\Models\Enquiry;

class MasterSeeder extends Seeder
{
    public function run()
    {
        Category::factory()->count(8)->create();
        Uom::factory()->count(6)->create();
        EnquiryType::factory()->count(5)->create();
        Source::factory()->count(5)->create();

        Company::factory()->count(5)->create();
        Supplier::factory()->count(6)->create();

        Project::factory()->count(6)->create();

        $categories = Category::all();
        $uoms = Uom::all();

        foreach (range(1,20) as $i) {
            Product::factory()->create([
                'category_id' => $categories->random()->id,
                'uom_id' => $uoms->random()->id,
            ]);
        }

        $projects = Project::all();
        $enquiryTypes = EnquiryType::all();
        $sources = Source::all();

        foreach (range(1,15) as $i) {
            Enquiry::factory()->create([
                'project_id' => $projects->random()->id,
                'enquiry_type_id' => $enquiryTypes->random()->id,
                'source_id' => $sources->random()->id,
            ]);
        }
    }
}
