<?php

namespace App;

use App\Inside\Constants;
use Illuminate\Database\Eloquent\Model;

class RoomEpisode extends Model
{
    protected $table = Constants::ROOM_EPISODE_DB;
    protected $fillable = [
        'app_id', 'room_id', 'supplier_id', 'capacity', 'price', 'type_percent',
        'percent', 'date', 'status'
    ];
    public $timestamps = false;
}
