<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\AdminUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Criar usuário padrão para testes
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Criar usuário admin
        AdminUser::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'), // Senha padrão
            'role' => 'admin',
            'settings' => json_encode([
                'receive_batch_notifications' => true,
                'receive_critical_notifications' => true
            ])
        ]);
    }
}