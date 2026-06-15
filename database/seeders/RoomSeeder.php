<?php

namespace Database\Seeders;

use App\Models\Room;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RoomSeeder extends Seeder
{
    public function run(): void
    {
        $floors = config('hotel.floors', [1, 2, 3]);
        
        foreach ($floors as $floor) {
            for ($i = 1; $i <= 10; $i++) {
                $roomNumber = $floor . str_pad($i, 2, '0', STR_PAD_LEFT);
                
                Room::firstOrCreate(
                    ['room_number' => $roomNumber],
                    [
                        'floor' => $floor,
                        'is_active' => true,
                        'qr_token' => Str::uuid()->toString(),
                    ]
                );
            }
        }
    }
}
