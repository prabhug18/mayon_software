<?php

namespace Database\Factories;

use App\Models\Enquiry;
use App\Models\Project;
use App\Models\EnquiryType;
use App\Models\Source;
use Illuminate\Database\Eloquent\Factories\Factory;

class EnquiryFactory extends Factory
{
    protected $model = Enquiry::class;

    public function definition()
    {
        $projectLocation = $this->faker->randomElement(['Mumbai','Bengaluru','Chennai','Hyderabad','Pune','Kochi','Jaipur','Surat','Noida','Gurgaon']);
    $roles = ['Site Engineer','Contractor','Vendor Contact','Procurement'];
    $firstNames = ['Rahul','Amit','Ravi','Suresh','Anil','Vikas','Deepak','Manoj','Prakash','Rohit','Sunil','Ajay'];
    $lastNames = ['Sharma','Patel','Reddy','Kumar','Singh','Gupta','Desai','Agarwal','Jain','Mehta','Iyer','Nair'];
    $baseName = $this->faker->randomElement($firstNames) . ' ' . $this->faker->randomElement($lastNames);
        return [
            'mobile' => $this->faker->optional()->numerify('+91##########'),
            'name' => $baseName . ' (' . $this->faker->randomElement($roles) . ')',
            'location' => $projectLocation,
            'enquiry_type_id' => function(){ return EnquiryType::inRandomOrder()->value('id') ?: EnquiryType::factory(); },
            'project_id' => function() use (&$projectLocation) {
                // try to find an existing project in the chosen city
                $loc = $projectLocation ?? null;
                if ($loc) {
                    $p = Project::where('location', $loc)->inRandomOrder()->first();
                    if ($p) return $p->id;
                }
                // fallback: create a new project with that city
                return Project::factory()->create(['location' => $projectLocation])->id ?? Project::factory();
            },
            'description' => $this->faker->optional()->randomElement([
                'Need 200 cubic meters of M-sand for new site',
                'Looking for quote on TMT steel bars for foundation',
                'Require 500 bags of cement 43 for slab',
                'Need delivery schedule for pipes and fittings',
                'Request site visit for material approval'
            ]),
            'status' => $this->faker->randomElement(['Open','In Progress','Closed']),
            'source_id' => function(){ return Source::inRandomOrder()->value('id') ?: Source::factory(); },
        ];
    }
}
