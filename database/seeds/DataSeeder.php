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
            'type_app_id' => 2,
            'title' => "سان رایز",
            'icon' => "",
            'type' => "percent",
            'percent' => 10
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
