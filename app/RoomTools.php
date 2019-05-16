<?php

namespace App;

use App\Inside\Constants;
use Illuminate\Database\Eloquent\Model;

class RoomTools extends Model
{
    protected $table = Constants::ROOM_TOOLS_DB;
    protected $fillable = [
        'type_app_id', 'room_id', 'icon', 'title', 'tooltip', 'created_at'
    ];
    public $timestamps = false;
}
