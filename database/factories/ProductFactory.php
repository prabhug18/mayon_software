<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Category;
use App\Models\Uom;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition()
    {
        $materials = ['MSand','River Sand','TMT Steel','Cement 43', 'Cement 53', 'Pipes 2"', 'Pipes 4"', 'Bricks', 'Tiles', 'Ready Mix Concrete'];
        return [
            // allow duplicate material names across products to avoid unique overflow
            'name' => $this->faker->randomElement($materials),
            // prefer an existing category/uom when seeding to avoid creating many unrelated rows
            'category_id' => function() {
                return Category::inRandomOrder()->value('id') ?: Category::factory();
            },
            'uom_id' => function() {
                return Uom::inRandomOrder()->value('id') ?: Uom::factory();
            },
            'main_image' => 'assets/images/uploads/' . $this->faker->unique()->lexify('product_????.jpg'),
        ];
    }
}
