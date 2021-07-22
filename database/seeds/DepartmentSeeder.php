<?php

use App\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $departments = [
            'General Management',
            'Human Resource Department',
            'IT Department'
        ];

        foreach($departments as $department) {
            Department::create([
                'name' => $department
            ]);
        }
        
    }
}
