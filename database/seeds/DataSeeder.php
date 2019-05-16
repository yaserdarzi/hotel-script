<?php

use Illuminate\Database\Seeder;

class DataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $hotel = \App\Hotel::create([
            'type_app_id' => 3,
            'title' => "سان رایز",
            'icon' => "",
            'type' => "percent",
            'percent' => 10
        ]);
        \App\HotelGallery::create([
            'type_app_id' => 3,
            'hotel_id' => $hotel->id,
            'path' => "01.jpg",
            'mime_type' => "image/jpg",
            'created_at' => date('Y-m-d')
        ]);
        \App\HotelGallery::create([
            'type_app_id' => 3,
            'hotel_id' => $hotel->id,
            'path' => "02.jpg",
            'mime_type' => "image/jpg",
            'created_at' => date('Y-m-d')
        ]);
        \App\HotelGallery::create([
            'type_app_id' => 3,
            'hotel_id' => $hotel->id,
            'path' => "03.jpg",
            'mime_type' => "image/jpg",
            'created_at' => date('Y-m-d')
        ]);
        \App\HotelGallery::create([
            'type_app_id' => 3,
            'hotel_id' => $hotel->id,
            'path' => "04.jpg",
            'mime_type' => "image/jpg",
            'created_at' => date('Y-m-d')
        ]);
        \App\Room::create([
            'type_app_id' => 3,
            'hotel_id' => $hotel->id,
            'title' => "اتاق دبل",
            'capacity' => 4,
            'count' => 1,
            'percent' => 5,
            'price' => 200000,
        ]);

    }
}
