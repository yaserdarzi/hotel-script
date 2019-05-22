<?php

namespace App;

use App\Inside\Constants;
use Illuminate\Database\Eloquent\Model;

class RoomEpisode extends Model
{
    protected $table = Constants::ROOM_EPISODE_DB;
    protected $fillable = [
        'app_id', 'hotel_id', 'room_id', 'supplier_id', 'capacity', 'price',
        'type_percent', 'percent', 'date', 'status'
    ];

    public function room()
    {
        return $this->hasOne(Room::class, 'id', 'room_id')->select('id', 'title')->where('deleted_at', null);
    }
}
