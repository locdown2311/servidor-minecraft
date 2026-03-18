<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user
        User::updateOrCreate(
            ['email' => 'admin@mchost.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'is_admin' => true,
            ]
        );

        // Demo user
        User::updateOrCreate(
            ['email' => 'jogador@mchost.com'],
            [
                'name' => 'Jogador',
                'password' => Hash::make('password'),
                'is_admin' => false,
            ]
        );

        // Plans
        Plan::updateOrCreate(
            ['slug' => 'starter'],
            [
                'name' => 'Starter',
                'ram_mb' => 2048,
                'max_players' => 10,
                'price_monthly' => 19.90,
                'description' => 'Ideal para jogar com amigos',
                'features' => [
                    '2 GB de RAM',
                    'Até 10 jogadores',
                    'SSD NVMe',
                    'Acesso FTP',
                    'Suporte por email',
                ],
                'sort_order' => 1,
            ]
        );

        Plan::updateOrCreate(
            ['slug' => 'pro'],
            [
                'name' => 'Pro',
                'ram_mb' => 4096,
                'max_players' => 30,
                'price_monthly' => 39.90,
                'description' => 'Para comunidades em crescimento',
                'features' => [
                    '4 GB de RAM',
                    'Até 30 jogadores',
                    'SSD NVMe',
                    'Acesso FTP',
                    'Suporte a mods (Paper/Forge)',
                    'Backups automáticos',
                    'Suporte prioritário',
                ],
                'sort_order' => 2,
            ]
        );

        Plan::updateOrCreate(
            ['slug' => 'ultimate'],
            [
                'name' => 'Ultimate',
                'ram_mb' => 8192,
                'max_players' => 100,
                'price_monthly' => 79.90,
                'description' => 'Máximo desempenho para grandes servidores',
                'features' => [
                    '8 GB de RAM',
                    'Até 100 jogadores',
                    'SSD NVMe',
                    'Acesso FTP',
                    'Todos os tipos de servidor',
                    'Backups automáticos',
                    'IP dedicado',
                    'Suporte 24/7',
                ],
                'sort_order' => 3,
            ]
        );
    }
}
