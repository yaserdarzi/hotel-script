<?php

namespace App;

use App\Inside\Constants;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Hotel extends Model
{
    use SoftDeletes;
    protected $table = Constants::HOTEL_DB;
    protected $fillable = [
        'type_app_id', 'title', 'icon', 'type', 'percent',
        'price', 'award'
    ];
    protected $dates = ['deleted_at'];
}
