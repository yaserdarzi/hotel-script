<?php

namespace App;

use App\Inside\Constants;
use Illuminate\Database\Eloquent\Model;

class HotelComment extends Model
{
    protected $casts = [
        'info' => 'object',
    ];
    protected $table = Constants::HOTEL_COMMENT_DB;
    protected $fillable = [
        'hotel_id', 'name', 'comment', 'path', 'info'
    ];
}