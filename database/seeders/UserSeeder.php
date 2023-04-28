<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        //
        $roleAdmin = Role::create(['name' => 'admin']);
        $roleNormal = Role::create(['name' => 'appUser']);
        $usersAdmin = User::factory(4)->create();
        $usersNormal = User::factory(10)->create();


        foreach($usersAdmin as $user) {
            $user->assignRole($roleAdmin);
        }
        foreach($usersNormal as $user) {
            $user->assignRole($roleNormal);
        }



    }
}
