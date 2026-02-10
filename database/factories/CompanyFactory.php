<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition()
    {
        $companies = [
            'Shree Construction Pvt Ltd', 'Apex Builders', 'Rao Infra', 'Kohli Builders', 'Ganesh Constructions',
            'Sundar Developers', 'Metro Buildtech', 'Triveni Construction', 'Vijay Projects', 'Nexus Infratech'
        ];
        return [
            'name' => $this->faker->unique()->randomElement($companies),
            'contact_person' => $this->faker->name(),
            'mobile' => '+91' . $this->faker->numerify('9#########'),
            'email' => $this->faker->unique()->safeEmail(),
            'address' => $this->faker->optional()->address(),
            'logo' => 'assets/images/uploads/' . $this->faker->unique()->lexify('company_????.jpg'),
            'gst_no' => strtoupper($this->faker->bothify('??#########')),
        ];
    }
}
