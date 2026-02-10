<?php

namespace Database\Factories;

use App\Models\Uom;
use Illuminate\Database\Eloquent\Factories\Factory;

class UomFactory extends Factory
{
    protected $model = Uom::class;

    public function definition()
    {
        $uoms = ['Kg','Ton','Piece','Meter','Sqft','Cubic Meter','Bag'];
        return [
            'name' => $this->faker->unique()->randomElement($uoms),
        ];
    }
}
