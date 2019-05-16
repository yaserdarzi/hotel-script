<?php

namespace App;

use App\Inside\Constants;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Hotel extends Model
{
    use SoftDeletes;
    protected $table = Constants::HOTEL_DB;
    protected $casts = [
        'global' => 'object',
        'possibilities' => 'object',
        'terms_of_use' => 'object',
    ];
    protected $fillable = [
        'type_app_id', 'title', 'icon', 'desc', 'address',
        'star', 'count_floor', 'count_room', 'delivery_room',
        'discharge_room', 'type', 'percent',
        'price', 'award', 'global', 'possibilities', 'terms_of_use',
        'lat', 'long'
    ];
    protected $dates = ['deleted_at'];


    public function gallery()
    {
        return $this->hasMany(HotelGallery::class, 'hotel_id', 'id')
            ->select('*', DB::raw("CASE WHEN path != '' THEN (concat ( '" . url('') . "/files/hotel/',hotel_id,'/', path) ) ELSE '' END as path"));
    }

    public function tools()
    {
        return $this->hasMany(HotelTools::class, 'hotel_id', 'id')
            ->select('*', DB::raw("CASE WHEN icon != '' THEN (concat ( '" . url('') . "/files/hotel/tools/', icon) ) ELSE '' END as icon"));
    }

    public function distance()
    {
        return $this->hasMany(HotelDistance::class, 'hotel_id', 'id');
    }

    public function rooms()
    {
        return $this->hasMany(Room::class, 'hotel_id', 'id')
            ->with("tools", "gallery")
            ->select('*', DB::raw("CASE WHEN image != '' THEN (concat ( '" . url('') . "/files/room/', image) ) ELSE '' END as image"));
    }


}
