<?php

namespace App;

use App\Inside\Constants;
use Illuminate\Database\Eloquent\Model;

class HotelTools extends Model
{
    protected $table = Constants::HOTEL_TOOLS_DB;
    protected $fillable = [
        'app_id', 'hotel_id', 'icon', 'title', 'tooltip', 'created_at'
    ];
    public $timestamps = false;
}
