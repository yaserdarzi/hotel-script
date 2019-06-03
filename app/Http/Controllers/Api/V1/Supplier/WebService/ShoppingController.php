<?php

namespace App\Http\Controllers\Api\V1\Supplier\WebService;

use App\Exceptions\ApiException;
use App\Http\Controllers\ApiController;
use App\Inside\Helpers;
use Illuminate\Http\Request;
use App\Http\Requests;

class ShoppingController extends ApiController
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
        //
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
        if (!$request->input('count'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن تعداد اجباری می باشد.'
            );
        if (!$request->input('phone'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن شماره همراه اجباری می باشد.'
            );
        if (!$request->input('name'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن نام و نام خانوادگی اجباری می باشد.'
            );
        if (!$request->input('room_id'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن شماره اتاق اجباری می باشد.'
            );
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
        $data = array(
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'room_id' => $request->input('room_id'),
            'name' => $request->input('name'),
            'phone' => $request->input('phone'),
            'desc' => $request->input('desc'),
            'tell' => $request->input('tell'),
            'email' => $request->input('email'),
            'base_url' => $request->input('base_url'),
            'count' => $request->input('count')
        );
        $curlShopping = curl_init();
        curl_setopt_array($curlShopping, array(
            CURLOPT_URL => env('CDN_AUTH_URL') . "/api/v1/cp/supplier/shoppingBag",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => array(
                "Accept: application/json",
                "AppName: " . $request->header("AppName"),
                "Authorization: " . $request->header('Authorization'),
                "appToken: " . $request->header('appToken'),
                "cache-control: no-cache",
                "content-type: multipart/form-data;"
            ),
        ));
        $responseShopping = curl_exec($curlShopping);
        $errShopping = curl_error($curlShopping);
        $infoShopping = curl_getinfo($curlShopping);
        curl_close($curlShopping);
        if ($errShopping)
            throw new ApiException(
                ApiException::EXCEPTION_BAD_REQUEST_400,
                $errShopping
            );
        if ($infoShopping['http_code'] != 200)
            throw new ApiException(
                ApiException::EXCEPTION_BAD_REQUEST_400,
                json_decode($responseShopping)->error
            );
        $curlPayment = curl_init();
        curl_setopt_array($curlPayment, array(
            CURLOPT_URL => env('CDN_AUTH_URL') . "/api/v1/cp/supplier/payment",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => array(
                "Accept: application/json",
                "AppName: " . $request->header("AppName"),
                "Authorization: " . $request->header('Authorization'),
                "appToken: " . $request->header('appToken'),
                "cache-control: no-cache",
                "market: " . $request->header('market'),
            ),
        ));
        $responsePayment = curl_exec($curlPayment);
        $errPayment = curl_error($curlPayment);
        $infoPayment = curl_getinfo($curlPayment);
        curl_close($curlPayment);
        if ($errPayment)
            throw new ApiException(
                ApiException::EXCEPTION_BAD_REQUEST_400,
                $errPayment
            );
        if ($infoPayment['http_code'] != 200)
            throw new ApiException(
                ApiException::EXCEPTION_BAD_REQUEST_400,
                json_decode($responsePayment)->error
            );
        return $this->respond(["url" => json_decode($responsePayment)->data->url]);
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
