<?php

namespace Database\Factories;

use App\Models\EnquiryType;
use Illuminate\Database\Eloquent\Factories\Factory;

class EnquiryTypeFactory extends Factory
{
    protected $model = EnquiryType::class;

    public function definition()
    {
        $types = ['Material Request','Quote Request','Site Visit','Complaint','Project Inquiry'];
        return [
            'name' => $this->faker->unique()->randomElement($types),
        ];
    }
}
