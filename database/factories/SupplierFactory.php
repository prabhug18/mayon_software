<?php

namespace Database\Factories;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

class SupplierFactory extends Factory
{
    protected $model = Supplier::class;

    public function definition()
    {
        $suppliers = [
            'Laxmi Cement Suppliers', 'Harsha Steel Traders', 'Sharma Concrete', 'Kumar Pipe Works', 'Saini Sand Suppliers',
            'Patel Building Materials', 'Reddy Aggregates', 'Mohan Hardware', 'Akash Roofing', 'Singh Tiles'
        ];
        $firstNames = ['Ravi','Amit','Sunil','Raj','Vikas','Suresh','Anil','Deepak','Manish','Rahul'];
        $lastNames = ['Sharma','Patel','Reddy','Kumar','Singh','Gupta','Desai','Agarwal','Jain','Mehta'];
        $domains = ['gmail.com','yahoo.in','hotmail.com','mgmail.com','outlook.in','companyemail.in'];
        $city = $this->faker->randomElement(['Mumbai','Bengaluru','Chennai','Hyderabad','Pune','Ahmedabad','Kolkata','Surat']);
        $state = $this->faker->randomElement(['Maharashtra','Karnataka','Tamil Nadu','Telangana','Gujarat','West Bengal']);
        $pincode = $this->faker->numerify('4####');
        return [
            'name' => $this->faker->unique()->randomElement($suppliers),
            'contact_person' => $this->faker->randomElement($firstNames) . ' ' . $this->faker->randomElement($lastNames),
            'mobile' => '+91' . $this->faker->numerify('9#########'),
            'email' => strtolower($this->faker->unique()->firstName() . '.' . $this->faker->lastName() . '@' . $this->faker->randomElement($domains)),
            'logo' => 'assets/images/uploads/' . $this->faker->unique()->lexify('supplier_????.jpg'),
            // suppliers table currently doesn't include address column; keep it realistic in other fields
            'gst_no' => strtoupper($this->faker->bothify('??#########')),
        ];
    }
}
