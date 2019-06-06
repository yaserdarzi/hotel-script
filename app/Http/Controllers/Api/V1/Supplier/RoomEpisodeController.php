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
use Morilog\Jalali\CalendarUtils;
use Morilog\Jalali\Jalalian;
use Morilog\Jalali\jDate;

class RoomEpisodeController extends ApiController
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
                'plz check your date'
            );
        $arrayDate = explode('/', $request->input('date'));
        $date = \Morilog\Jalali\CalendarUtils::toGregorian($arrayDate[0], $arrayDate[1], $arrayDate[2]);
        $date = date('Y-m-d', strtotime($date[0] . '-' . $date[1] . '-' . $date[2]));
        $roomEpisode = RoomEpisode::where('app_id', $request->input('app_id'))
            ->with('room')
            ->where(['hotel_id' => $hotel_id, 'supplier_id' => $request->input('supplier_id'), 'date' => $date])
            ->select(
                "id",
                "app_id",
                "hotel_id",
                "room_id",
                "supplier_id",
                "capacity",
                "capacity_filled",
                "capacity_remaining",
                "price",
                "type_percent",
                "percent",
                "date",
                "status",
                'is_capacity',
                'add_price'
            )->get()->map(function ($value) {
                $value->date_persian = CalendarUtils::strftime('Y-m-d', strtotime($value->date));
                return $value;
            });
        return $this->respond($roomEpisode);
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
        if ($request->input('role') != Constants::ROLE_ADMIN)
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی شما دسترسی به این قسمت ندارید.'
            );
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
        if (!Room::where('app_id', $request->input('app_id'))->where(['hotel_id' => $hotel_id, 'id' => $request->input('room_id')])->exists())
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'plz check your room_id'
            );
        if (!$request->input('capacity'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن ظرفیت (تعداد اتاق) اجباری می باشد.'
            );
        if (!$request->input('price'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن قیمت اجباری می باشد.'
            );
        switch ($request->input('type_percent')) {
            case Constants::TYPE_PERCENT_PRICE:
                $typePercent = Constants::TYPE_PERCENT_PRICE;
                break;
            case Constants::TYPE_PERCENT_PERCENT:
                $typePercent = Constants::TYPE_PERCENT_PERCENT;
                break;
            default:
                $typePercent = Constants::TYPE_PERCENT_PRICE;
        }
        if (!$request->input('start_date'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن تاریخ شروع اجباری می باشد.'
            );
        if (!$request->input('end_date'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن تاریخ پایان اجباری می باشد.'
            );
        $is_capacity = false;
        if ($request->input('is_capacity'))
            $is_capacity = true;
        $arrayStartDate = explode('/', $request->input('start_date'));
        $arrayEndDate = explode('/', $request->input('end_date'));
        $start_date = \Morilog\Jalali\CalendarUtils::toGregorian($arrayStartDate[0], $arrayStartDate[1], $arrayStartDate[2]);
        $end_date = \Morilog\Jalali\CalendarUtils::toGregorian($arrayEndDate[0], $arrayEndDate[1], $arrayEndDate[2]);
        $startDay = date_create(date('Y-m-d', strtotime($start_date[0] . '-' . $start_date[1] . '-' . $start_date[2])));
        $endDay = date_create(date('Y-m-d', strtotime($end_date[0] . '-' . $end_date[1] . '-' . $end_date[2])));
        $diff = date_diff($startDay, $endDay);
        for ($i = 0; $i <= $diff->days; $i++) {
            $date = strtotime(date('Y-m-d', strtotime($startDay->format('Y-m-d') . " +" . $i . " days")));
            $status = RoomEpisode::where([
                'app_id' => $request->input('app_id'),
                'hotel_id' => $hotel_id,
                'room_id' => $request->input('room_id'),
                'supplier_id' => $request->input('supplier_id'),
                'date' => date('Y-m-d', $date),
            ])->exists();
            if (!$status)
                RoomEpisode::create([
                    'app_id' => $request->input('app_id'),
                    'hotel_id' => $hotel_id,
                    'room_id' => $request->input('room_id'),
                    'supplier_id' => $request->input('supplier_id'),
                    'capacity' => $this->help->normalizePhoneNumber($request->input('capacity')),
                    'capacity_remaining' => $this->help->normalizePhoneNumber($request->input('capacity')),
                    'price' => $this->help->priceNumberDigitsToNormal($request->input('price')),
                    'type_percent' => $typePercent,
                    'percent' => $this->help->normalizePhoneNumber($request->input('percent')),
                    'date' => date('Y-m-d', $date),
                    'is_capacity' => $is_capacity,
                    'add_price' => $this->help->normalizePhoneNumber($request->input('add_price')),
                ]);
        }
        return $this->respond(["status" => "success"]);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($hotel_id, Request $request, $id)
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
        if (!RoomEpisode::where('app_id', $request->input('app_id'))->where(['id' => $id, 'supplier_id' => $request->input('supplier_id'), 'hotel_id' => $hotel_id])->exists())
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'plz check your id'
            );
        $roomEpisode = RoomEpisode::where('app_id', $request->input('app_id'))
            ->with('room')
            ->where(['hotel_id' => $hotel_id, 'supplier_id' => $request->input('supplier_id'), 'id' => $id])
            ->select(
                "id",
                "app_id",
                "hotel_id",
                "room_id",
                "supplier_id",
                "capacity",
                "capacity_filled",
                "capacity_remaining",
                "price",
                "type_percent",
                "percent",
                "date",
                "status",
                'is_capacity',
                'add_price'
            )->first();
        $roomEpisode->date_persian = CalendarUtils::strftime('Y-m-d', strtotime($roomEpisode->date));
        return $this->respond($roomEpisode);
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
        if ($request->input('role') != Constants::ROLE_ADMIN)
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی شما دسترسی به این قسمت ندارید.'
            );
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
        if (!RoomEpisode::where('app_id', $request->input('app_id'))->where(['id' => $id, 'supplier_id' => $request->input('supplier_id'), 'hotel_id' => $hotel_id])->exists())
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'plz check your id'
            );
        if (!$roomEpisode = RoomEpisode::where('app_id', $request->input('app_id'))->where(['id' => $id, 'supplier_id' => $request->input('supplier_id'), 'hotel_id' => $hotel_id])->where('status', Constants::STATUS_ACTIVE)->first())
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، امکان تغییر این سانس وجود ندارد.'
            );
        if (!$request->input('capacity'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن ظرفیت (تعداد اتاق) اجباری می باشد.'
            );
        if (!$request->input('price'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن قیمت اجباری می باشد.'
            );
        switch ($request->input('type_percent')) {
            case Constants::TYPE_PERCENT_PRICE:
                $typePercent = Constants::TYPE_PERCENT_PRICE;
                break;
            case Constants::TYPE_PERCENT_PERCENT:
                $typePercent = Constants::TYPE_PERCENT_PERCENT;
                break;
            default:
                $typePercent = Constants::TYPE_PERCENT_PRICE;
        }
        switch ($request->input('status')) {
            case Constants::STATUS_ACTIVE:
                $status = Constants::STATUS_ACTIVE;
                break;
            case Constants::STATUS_DEACTIVATE:
                $status = Constants::STATUS_DEACTIVATE;
                break;
            case Constants::STATUS_RETURN_BUY:
                $status = Constants::STATUS_RETURN_BUY;
                break;
            default:
                throw new ApiException(
                    ApiException::EXCEPTION_NOT_FOUND_404,
                    'کاربر گرامی ، وارد کردن وضعیت اجباری می باشد.'
                );
        }
        if (!$request->input('date'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن تاریخ اجباری می باشد.'
            );
        if ($request->input('capacity') < $roomEpisode->capacity_filled)
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، امکان کم کردن ظرفیت وجود ندارد.'
            );
        $is_capacity = false;
        if ($request->input('is_capacity'))
            $is_capacity = true;
        $arrayDate = explode('/', $request->input('date'));
        if (sizeof($arrayDate) == 1)
            $arrayDate = explode('-', $request->input('date'));
        $date = \Morilog\Jalali\CalendarUtils::toGregorian($arrayDate[0], $arrayDate[1], $arrayDate[2]);
        $date = date_create(date('Y-m-d', strtotime($date[0] . '-' . $date[1] . '-' . $date[2])));
        RoomEpisode::where('id', $id)->update([
            'capacity' => $this->help->normalizePhoneNumber($request->input('capacity')),
            'capacity_remaining' => intval($this->help->normalizePhoneNumber($request->input('capacity')) - $roomEpisode->capacity_filled),
            'price' => $this->help->priceNumberDigitsToNormal($request->input('price')),
            'type_percent' => $typePercent,
            'percent' => $this->help->normalizePhoneNumber($request->input('percent')),
            'date' => $date->format('Y-m-d'),
            'status' => $status,
            'is_capacity' => $is_capacity,
            'add_price' => $this->help->normalizePhoneNumber($request->input('add_price')),
        ]);
        return $this->respond(["status" => "success"]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($hotel_id, $id, Request $request)
    {
        if ($request->input('role') != Constants::ROLE_ADMIN)
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی شما دسترسی به این قسمت ندارید.'
            );
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
        if (!RoomEpisode::where('app_id', $request->input('app_id'))->where(['id' => $id, 'supplier_id' => $request->input('supplier_id'), 'hotel_id' => $hotel_id])->exists())
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'plz check your id'
            );
        if (!$roomEpisode = RoomEpisode::where('app_id', $request->input('app_id'))->where(['id' => $id, 'supplier_id' => $request->input('supplier_id'), 'hotel_id' => $hotel_id])->where('status', Constants::STATUS_ACTIVE)->first())
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، امکان حذف این سانس وجود ندارد.'
            );
        if ($roomEpisode->capacity_filled != 0)
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، امکان حذف این سانس وجود ندارد.'
            );
        RoomEpisode::where('id', $id)->delete();
        return $this->respond(["status" => "success"]);
    }

    ///////////////////public function///////////////////////


}
