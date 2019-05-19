<?php

namespace App\Http\Controllers\Api\V1\Supplier;

use App\Exceptions\ApiException;
use App\Hotel;
use App\HotelSupplier;
use App\Http\Controllers\ApiController;
use App\Inside\Constants;
use App\Inside\Helpers;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Facades\Image;

class HotelController extends ApiController
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
    public function index(Request $request)
    {
        $hotel = Hotel::where('app_id', $request->input('app_id'))
            ->select('id', 'name')->get();
        return $this->respond($hotel);
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
    public function store(Request $request)
    {
        if (!$request->input('name'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن نام اجباری می باشد.'
            );
        if (!$request->input('address'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن ترتیب نشانی اجباری می باشد.'
            );
        if (!$request->input('star'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن ستاره اجباری می باشد.'
            );
        if (!in_array($request->file('logo')->getClientMimeType(), Constants::PHOTO_TYPE))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن لوگو اجباری می باشد.'
            );
        if (!$request->input('count_floor'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن تعداد طبقات اجباری می باشد.'
            );
        if (!$request->input('count_room'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن تعداد اتاق ها اجباری می باشد.'
            );
        if (!$request->input('delivery_room'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن ساعت تحویل اتاق اجباری می باشد.'
            );
        if (!$request->input('discharge_room'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن ساعت تخلیه اتاق اجباری می باشد.'
            );
        if (!$request->input('about'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن درباره هتل اجباری می باشد.'
            );
        \Storage::disk('upload')->makeDirectory('/hotel/', 0777, true, true);
        \Storage::disk('upload')->makeDirectory('/hotel/thumb/', 0777, true, true);
        $logo = md5(\File::get($request->file("logo"))) . '.' . $request->file("logo")->getClientOriginalExtension();
        $exists = \Storage::disk('upload')->has('/hotel/' . $logo);
        if ($exists == null) {
            \Storage::disk('upload')->put('/hotel/' . $logo, \File::get($request->file("logo")->getRealPath()));
        }
        //generate thumbnail
        $image_resize = Image::make($request->file("logo")->getRealPath());
        //get width and height of image
        $data = getimagesize($request->file("logo"));
        $imageWidth = $data[0];
        $imageHeight = $data[1];
        $newDimen = $this->help->getScaledDimension($imageWidth, $imageHeight, 200, 200, false);
        $image_resize->resize($newDimen[0], $newDimen[1]);
        $thumb = public_path('/files/hotel/thumb/' . $logo);
        $image_resize->save($thumb);
        $hotel = Hotel::create([
            'app_id' => $request->input('app_id'),
            'name' => $request->input('name'),
            'address' => $request->input('address'),
            'star' => $request->input('star'),
            'logo' => $logo,
            'count_floor' => $request->input('count_floor'),
            'count_room' => $request->input('count_room'),
            'delivery_room' => $request->input('delivery_room'),
            'discharge_room' => $request->input('discharge_room'),
            'about' => $request->input('about'),
        ]);
        HotelSupplier::create([
            'app_id' => $request->input('app_id'),
            'supplier_id' => $request->input('supplier_id'),
            'hotel_id' => $hotel->id,
        ]);
        return $this->respond(["status" => "success"]);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $hotel_id)
    {
        $hotel = Hotel::where('app_id', $request->input('app_id'))
            ->with("gallery")
            ->where(['id' => $hotel_id])
            ->select(
                '*',
                DB::raw("CASE WHEN logo != '' THEN (concat ( '" . url('') . "/files/hotel/', logo) ) ELSE '' END as logo"),
                DB::raw("CASE WHEN logo != '' THEN (concat ( '" . url('') . "/files/hotel/thumb/', logo) ) ELSE '' END as logo_thumb")
            )
            ->first();
        return $this->respond($hotel);
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
    public function update(Request $request, $id)
    {
        $hotelInfo = Hotel::where('app_id', $request->input('app_id'))
            ->where(['id' => $id])->first();
        if (!$hotelInfo)
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'plz check your id'
            );
        if (!$request->input('name'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن نام اجباری می باشد.'
            );
        if (!$request->input('address'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن ترتیب نشانی اجباری می باشد.'
            );
        if (!$request->input('star'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن ستاره اجباری می باشد.'
            );
        if (!$request->input('count_floor'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن تعداد طبقات اجباری می باشد.'
            );
        if (!$request->input('count_room'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن تعداد اتاق ها اجباری می باشد.'
            );
        if (!$request->input('delivery_room'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن ساعت تحویل اتاق اجباری می باشد.'
            );
        if (!$request->input('discharge_room'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن ساعت تخلیه اتاق اجباری می باشد.'
            );
        if (!$request->input('about'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن درباره هتل اجباری می باشد.'
            );
        $logo = $hotelInfo->logo;
        if ($request->file('logo')) {
            if (!in_array($request->file('logo')->getClientMimeType(), Constants::PHOTO_TYPE))
                throw new ApiException(
                    ApiException::EXCEPTION_NOT_FOUND_404,
                    'کاربر گرامی ، وارد کردن لوگو اجباری می باشد.'
                );
            \Storage::disk('upload')->makeDirectory('/hotel/', 0777, true, true);
            \Storage::disk('upload')->makeDirectory('/hotel/thumb/', 0777, true, true);
            $logo = md5(\File::get($request->file("logo"))) . '.' . $request->file("logo")->getClientOriginalExtension();
            $exists = \Storage::disk('upload')->has('/hotel/' . $logo);
            if ($exists == null) {
                \Storage::disk('upload')->put('/hotel/' . $logo, \File::get($request->file("logo")->getRealPath()));
            }
            //generate thumbnail
            $image_resize = Image::make($request->file("logo")->getRealPath());
            //get width and height of image
            $data = getimagesize($request->file("logo"));
            $imageWidth = $data[0];
            $imageHeight = $data[1];
            $newDimen = $this->help->getScaledDimension($imageWidth, $imageHeight, 200, 200, false);
            $image_resize->resize($newDimen[0], $newDimen[1]);
            $thumb = public_path('/files/hotel/thumb/' . $logo);
            $image_resize->save($thumb);
        }
        Hotel::where('id', $id)->update([
            'name' => $request->input('name'),
            'address' => $request->input('address'),
            'star' => $request->input('star'),
            'logo' => $logo,
            'count_floor' => $request->input('count_floor'),
            'count_room' => $request->input('count_room'),
            'delivery_room' => $request->input('delivery_room'),
            'discharge_room' => $request->input('discharge_room'),
            'about' => $request->input('about'),
        ]);
        return $this->respond(["status" => "success"]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Request $request)
    {
        if (!Hotel::where('app_id', $request->input('app_id'))->where(['id' => $id])->exists())
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'plz check your id'
            );
        Hotel::where('id', $id)->delete();
        return $this->respond(["status" => "success"]);
    }

    ///////////////////public function///////////////////////


}