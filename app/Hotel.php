<?php

namespace App;

use App\Inside\Constants;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Hotel extends Model
{
    use SoftDeletes;
    protected $table = Constants::HOTEL_DB;
    protected $fillable = [
        'type_app_id', 'title', 'icon', 'type', 'percent',
        'price', 'award'
    ];
    protected $dates = ['deleted_at'];


    public function gallery()
    {
        return $this->hasMany(HotelGallery::class, 'hotel_id', 'id')
            ->select('*', DB::raw("CASE WHEN path != '' THEN (concat ( '" . url('') . "/files/hotel/',hotel_id,'/', path) ) ELSE '' END as path"));
    }
}
