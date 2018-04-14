<?php
/**
 * Copyright (c) 2018.
 * @author Antony [leantony] Chacha
 */

use Faker\Generator as Faker;
use Leantony\Grid\Test\Models\Role;
use Leantony\Grid\Test\Models\User;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(Role::class, function (Faker $faker) {
    return [
        'name' => str_random(6),
        'description' => $faker->text(),
    ];
});

$factory->define(User::class, function (Faker $faker) {
    $role = Role::all()->random();
    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'password' => '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', // secret
        'role_id' => $role->id,
        'remember_token' => str_random(10),
    ];
});
