<?php

namespace App\Http\Controllers\Api\V1\Api;

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
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => env('CDN_AUTH_URL') . "/api/v1/cp/api/app/get/supplier",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "",
            CURLOPT_HTTPHEADER => array(
                "Accept: application/json",
                "sales: api",
                "Authorization: " . $request->header('Authorization'),
                "appToken: " . $request->header('appToken')
            ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        $info = curl_getinfo($curl);
        curl_close($curl);
        if ($err)
            throw new ApiException(
                ApiException::EXCEPTION_BAD_REQUEST_400,
                $err
            );
        if ($info['http_code'] != 200)
            throw new ApiException(
                ApiException::EXCEPTION_BAD_REQUEST_400,
                json_decode($response)->error
            );
        $supplierID = json_decode($response)->data;
        $hotel = Hotel::join(Constants::HOTEL_SUPPLIER_DB, Constants::HOTEL_DB . '.id', '=', Constants::HOTEL_SUPPLIER_DB . '.hotel_id')
            ->whereIn('supplier_id', $supplierID)
            ->where([
                Constants::HOTEL_DB . '.app_id' => $request->input('app_id'),
                Constants::HOTEL_SUPPLIER_DB . '.app_id' => $request->input('app_id'),
            ])
            ->select(
                Constants::HOTEL_DB . '.id as hotel_id',
                'name',
                DB::raw("CASE WHEN logo != '' THEN (concat ( '" . url('') . "/files/hotel/thumb/', logo) ) ELSE '' END as logo_thumb")
            )->get();
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $hotel_id)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => env('CDN_AUTH_URL') . "/api/v1/cp/api/app/get/supplier",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "",
            CURLOPT_HTTPHEADER => array(
                "Accept: application/json",
                "sales: api",
                "Authorization: " . $request->header('Authorization'),
                "appToken: " . $request->header('appToken')
            ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        $info = curl_getinfo($curl);
        curl_close($curl);
        if ($err)
            throw new ApiException(
                ApiException::EXCEPTION_BAD_REQUEST_400,
                $err
            );
        if ($info['http_code'] != 200)
            throw new ApiException(
                ApiException::EXCEPTION_BAD_REQUEST_400,
                json_decode($response)->error
            );
        $supplierID = json_decode($response)->data;
        if (!HotelSupplier::where(['hotel_id' => $hotel_id])->whereIn('supplier_id', $supplierID)->exists())
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی شما دسترسی به این قسمت ندارید.'
            );
        $hotel = Hotel::where('app_id', $request->input('app_id'))
            ->with("gallery")
            ->where(['id' => $hotel_id])
            ->select(
                'id',
                'id as hotel_id',
                'name',
                'about',
                'address',
                'rule',
                'recovery',
                'star',
                'count_floor',
                'count_room',
                'delivery_room',
                'discharge_room',
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Request $request)
    {
        //
    }

    ///////////////////public function///////////////////////


}
