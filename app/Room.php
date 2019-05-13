<?php

namespace App;

use App\Inside\Constants;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Room extends Model
{
    use SoftDeletes;
    protected $casts = [
        'service' => 'object',
    ];
    protected $table = Constants::ROOM_DB;
    protected $fillable = [
        'type_app_id', 'hotel_id', 'title', 'image', 'desc',
        'service', 'capacity', 'count', 'percent', 'price', 'sort'
    ];
    protected $dates = ['deleted_at'];
}
