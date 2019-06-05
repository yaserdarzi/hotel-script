<?php

namespace App;

use App\Inside\Constants;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RoomEpisode extends Model
{
    protected $table = Constants::ROOM_EPISODE_DB;
    protected $fillable = [
        'app_id', 'hotel_id', 'room_id', 'supplier_id', 'capacity',
        'capacity_filled', 'capacity_remaining', 'price',
        'type_percent', 'percent', 'date', 'is_capacity',
        'add_price', 'status'
    ];

    public function room()
    {
        return $this->hasOne(Room::class, 'id', 'room_id')
            ->select(
                'id',
                'title',
                DB::raw("CASE WHEN image != '' THEN (concat ( '" . url('') . "/files/hotel/',hotel_id,'/room/thumb/', image) ) ELSE '' END as image_thumb")
            )
            ->where('deleted_at', null);
    }

    public function hotel()
    {
        return $this->hasOne(Hotel::class, 'id', 'hotel_id')
            ->select(
                'id',
                'name',
                DB::raw("CASE WHEN logo != '' THEN (concat ( '" . url('') . "/files/hotel/thumb/', logo) ) ELSE '' END as logo_thumb")
            )
            ->where('deleted_at', null);
    }

}
