<?php

namespace Database\Factories;

use App\Models\Source;
use Illuminate\Database\Eloquent\Factories\Factory;

class SourceFactory extends Factory
{
    protected $model = Source::class;

    public function definition()
    {
        $sources = ['Phone','WhatsApp','Website','Walk-in','Referral','Email'];
        return [
            'name' => $this->faker->unique()->randomElement($sources),
        ];
    }
}
