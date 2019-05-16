<?php

namespace App;

use App\Inside\Constants;
use Illuminate\Database\Eloquent\Model;

class HotelDistance extends Model
{
    protected $table = Constants::HOTEL_DISTANCE_DB;
    protected $fillable = [
        'type_app_id', 'hotel_id', 'title', 'link', 'distance', 'created_at'
    ];
    public $timestamps = false;
}
