<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition()
    {
        $cats = ['Construction Material','Steel','Pipes','Tiles','Cement','Aggregate','Hardware','Electrical'];
        return [
            'name' => $this->faker->unique()->randomElement($cats),
        ];
    }
}
