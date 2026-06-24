<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Tekrar tekrar çalıştırılsa bile (örn. `php artisan db:seed`)
     * aynı admin kaydını çoğaltmaz — email üzerinden eşleşir,
     * yoksa oluşturur.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@mafia.com'],
            [
                'nickname' => 'Suhan',
                'password' => 'begenjov', 
                'xp' => 5000,
                'rank' => 'rookie',
                'total_played' => 0,
                'total_wins' => 0,
                'total_losses' => 0,
                'is_admin' => true,
            ]
        );
    }
}
