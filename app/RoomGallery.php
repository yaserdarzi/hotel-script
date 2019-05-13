<?php

namespace App;

use App\Inside\Constants;
use Illuminate\Database\Eloquent\Model;

class RoomGallery extends Model
{
    protected $table = Constants::ROOM_GALLERY_DB;
    protected $fillable = [
        'type_app_id', 'room_id', 'path', 'mime_type', 'created_at'
    ];
    public $timestamps = false;
}
