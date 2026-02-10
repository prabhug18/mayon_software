<?php

namespace Database\Factories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition()
    {
        $statuses = ['On Going','Completed'];
        $cities = ['Mumbai','Bengaluru','Chennai','Hyderabad','Pune','Ahmedabad','Kolkata','Surat','Jaipur','Lucknow'];
        $types = ['Residency', 'Commercial Complex', 'Industrial Park', 'Logistics Hub', 'Mall', 'Apartment Block'];
        return [
            'name' => $this->faker->unique()->company() . ' ' . $this->faker->randomElement($types),
            'location' => $this->faker->randomElement($cities),
            'status' => $this->faker->randomElement($statuses),
            'logo_image' => 'assets/images/uploads/' . $this->faker->unique()->lexify('project_????.jpg'),
            'address' => $this->faker->optional()->address(),
        ];
    }
}
