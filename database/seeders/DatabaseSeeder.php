<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents; // Bisa tetap ada atau dihapus
use Illuminate\Database\Seeder;
use App\Models\User; // Tetap ada karena Anda membuat user admin
// Hapus imports untuk TouristAttraction, Ticket, Booking jika tidak digunakan lagi
// use App\Models\TouristAttraction;
// use App\Models\Ticket;
// use App\Models\Booking;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // Panggilan ke seeder lain (TouristAttractionSeeder, ReviewSeeder) dihapus/dikomentari.
        // Bagian membuat user reguler dihapus.
        // Bagian membuat atraksi dan tiket di dalam foreach dihapus.
        // Bagian membuat booking dihapus.

        // Create admin user (INI TETAPKAN, ini adalah satu-satunya data yang akan dibuat)
        User::updateOrCreate([
            'email' => 'admin@example.com',
        ], [
            'name' => 'Admin User',
            'password' => Hash::make('password'),
            'is_admin' => true,
            'role' => 'admin', // Pastikan 'role' ada dan 'admin' seperti yang kita bahas sebelumnya
        ]);
    }
}