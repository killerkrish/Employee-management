<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Division;
use App\Department;



$factory->define(App\Employee::class, function (Faker\Generator $faker) {
    $department = Department::inRandomOrder()->first();
    $division = Division::inRandomOrder()->first();

    return [
        'lastname' => $faker->lastName,
        'firstname' => $faker->firstName,
        'email' => 'krishnansmart17@gmail.com',
        'password' => str_random(8),
        'address' => $faker->address,
        'age' => rand(18, 50),
        'birthdate' => $faker->dateTimeBetween('-30 years', '-20 years'),
        'date_hired' => \Carbon\Carbon::now()->format('Y-m-d'),
        'department_id' => $department->id,
        'division_id' => $division->id,
        'picture' => ''
    ];
});