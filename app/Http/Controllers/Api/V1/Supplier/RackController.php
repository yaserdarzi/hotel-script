<?php

namespace App\Http\Controllers\Api\V1\Supplier;

use App\Exceptions\ApiException;
use App\Hotel;
use App\HotelSupplier;
use App\Http\Controllers\ApiController;
use App\Inside\Constants;
use App\Inside\Helpers;
use App\Room;
use App\RoomEpisode;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Facades\Image;
use Morilog\Jalali\CalendarUtils;

class RackController extends ApiController
{
    protected $help;

    public function __construct()
    {
        $this->help = new Helpers();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($hotel_id, Request $request)
    {
        if (!HotelSupplier::where(['supplier_id' => $request->input('supplier_id'), 'hotel_id' => $hotel_id])->exists())
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی شما دسترسی به این قسمت ندارید.'
            );
        if (!Hotel::where('app_id', $request->input('app_id'))->where(['id' => $hotel_id])->exists())
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'plz check your hotel_id'
            );
        if (!$request->input('date'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی لطفا تاریخ را وارد نمایید.'
            );
        $arrayDate = explode('/', $request->input('date'));
        $dateExplode = \Morilog\Jalali\CalendarUtils::toGregorian($arrayDate [0], $arrayDate [1], $arrayDate [2]);
        $persianFirstDayMouth = CalendarUtils::strftime('Y-m-01', strtotime($dateExplode[0] . '-' . $dateExplode[1] . '-' . $dateExplode[2]));
        $persianEndDayMouth = CalendarUtils::strftime('Y-m-t', strtotime($dateExplode[0] . '-' . $dateExplode[1] . '-' . $dateExplode[2]));
        $perisanFirstExplode = \Morilog\Jalali\CalendarUtils::toGregorian(explode('-', $persianFirstDayMouth)[0], explode('-', $persianFirstDayMouth)[1], explode('-', $persianFirstDayMouth)[2]);
        $perisanEndExplode = \Morilog\Jalali\CalendarUtils::toGregorian(explode('-', $persianEndDayMouth)[0], explode('-', $persianEndDayMouth)[1], explode('-', $persianEndDayMouth)[2]);
        $startDay = date_create(date('Y-m-d', strtotime($perisanFirstExplode[0] . '-' . $perisanFirstExplode[1] . '-' . $perisanFirstExplode[2])));
        $endDay = date_create(date('Y-m-d', strtotime($perisanEndExplode [0] . '-' . $perisanEndExplode [1] . '-' . $perisanEndExplode [2])));
        $diff = date_diff($startDay, $endDay);
        $room = Room::where('app_id', $request->input('app_id'))
            ->where(['hotel_id' => $hotel_id])
            ->select(
                'id',
                'title',
                DB::raw("CASE WHEN image != '' THEN (concat ( '" . url('') . "/files/hotel/',hotel_id,'/room/thumb/', image) ) ELSE '' END as image_thumb")
            )->get();
        foreach ($room as $value) {
            $episode = [];
            for ($i = 0; $i <= $diff->days; $i++) {
                $date = strtotime(date('Y-m-d', strtotime($startDay->format('Y-m-d') . " +" . $i . " days")));
                $roomEpisode = RoomEpisode::where([
                    'app_id' => $request->input('app_id'),
                    'hotel_id' => $hotel_id,
                    'room_id' => $value->id,
                    'supplier_id' => $request->input('supplier_id'),
                    'date' => date('Y-m-d', $date),
                ])->first();
                if ($roomEpisode) {
                    if ($roomEpisode->capacity_remaining == 0)
                        $data['status'] = Constants::ROOM_STATUS_FULL;
                    elseif ($roomEpisode->capacity == $roomEpisode->capacity_remaining)
                        $data['status'] = Constants::ROOM_STATUS_EMPTY;
                    else
                        $data['status'] = Constants::ROOM_STATUS_RESERVABLE;
                    $data['capacity'] = $roomEpisode->capacity;
                    $data['capacity_filled'] = $roomEpisode->capacity_filled;
                    $data['capacity_remaining'] = $roomEpisode->capacity_remaining;
                } else {
                    $data['status'] = Constants::ROOM_STATUS_UNDEFINED;
                    $data['capacity'] = 0;
                    $data['capacity_filled'] = 0;
                    $data['capacity_remaining'] = 0;
                }
                $data['date'] = CalendarUtils::strftime('Y-m-d', $date);
                $episode[$i] = $data;
            }
            $value->episode = $episode;
        }
        return $this->respond($room);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store($hotel_id, Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($hotel_id, Request $request, $room_id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update($hotel_id, Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($hotel_id, $id, Request $request)
    {
        //
    }

    ///////////////////public function///////////////////////


}
