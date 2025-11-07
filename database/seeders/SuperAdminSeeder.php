<?php  

namespace Database\Seeders;  

use App\Models\User;  
use App\Enums\UserRole; // use your existing enum  
use Illuminate\Database\Seeder;  
use Illuminate\Support\Facades\Hash;  

class SuperAdminSeeder extends Seeder  
{  
    public function run(): void  
    {  
        User::updateOrCreate(  
            ['email' => 'supera.internship@gmail.com'], // 👈 change to your real SuperAdmin email  
            [  
                'name'         => 'Super Admin',  
                'password'     => Hash::make('creator123!'), // 👈 change password if you want  
                'role'         => UserRole::Admin, // must match one of your enum values  
                'is_creator'   => true,  // extra flag to mark this as the "super admin"  
                'is_approved'  => true,  
                'approved_at'  => now(),  
                'approved_by'  => null,  
            ]  
        );  
    }  
}  
