<?php

namespace Database\Seeders;

use App\Models\Requirement;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Role::firstOrCreate(['name' => 'user']);
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'super-admin']);

        $this->call(CollegesAndDepartmentsSeeder::class);

        // random users
        User::factory(150)->create();
        Requirement::factory(100)->create();

        $user = User::create([
            'firstname' => 'Doming',
            'middlename' => 'H.',
            'lastname' => 'Ricalde',
            'extensionname' => '',
            'email' => 'domingricalde@gmail.com',
            'college_id' => '1',
            'department_id' => '1',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
        ]);
        $user->assignRole('user');

        $admin = User::create([
            'firstname' => 'sample',
            'middlename' => '',
            'lastname' => 'admin',
            'extensionname' => '',
            'email' => 'admin@gmail.com',
            'college_id' => '1',
            'department_id' => '1',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole('admin');

        $superAdmin = User::create([
            'firstname' => 'sample',
            'middlename' => '',
            'lastname' => 'super admin',
            'extensionname' => '',
            'email' => 'superadmin@gmail.com',
            'college_id' => '1',
            'department_id' => '1',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
        ]);
        $superAdmin->assignRole('super-admin');
    }
}
