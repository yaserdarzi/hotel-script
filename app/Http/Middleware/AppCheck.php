<?php

namespace App\Http\Middleware;

use App\Exceptions\ApiException;
use App\Inside\Constants;
use Closure;
use Firebase\JWT\JWT;

class AppCheck
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     *
     * @return mixed
     * @throws \App\Exceptions\ApiException
     */
    public function handle($request, Closure $next)
    {
        $input = array_map(function ($input) {
            if (is_array($input)) {
                return array_map(array($this, 'safeTrim'), $input);
            }
            return trim($input);
        }, $request->all());
        if (!$request->header('appToken'))
            throw new ApiException(
                ApiException::EXCEPTION_BAD_REQUEST_400,
                'Plz check your appToken header'
            );
        $appToken = JWT::decode($request->header('appToken'), config("jwt.secret"), array('HS256'));
        if ($appToken->app != Constants::APP || $appToken->type_app != Constants::TYPE_APP)
            throw new ApiException(
                ApiException::EXCEPTION_BAD_REQUEST_400,
                'Plz check your appToken header'
            );
        if ($appToken->type_app_child == "") {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => env('CDN_AUTH_URL') . "/api/v1/app",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_POSTFIELDS => "",
                CURLOPT_HTTPHEADER => array(
                    "Accept: application/json",
                    "Postman-Token: 014f0d51-e8a3-4346-babd-9e6ea9e7aef2",
                    "app: " . $appToken->app,
                    "cache-control: no-cache",
                    "typeApp: " . $appToken->type_app
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
            $input['app_id'] = json_decode($response)->data->app_id;
        } else {
            if (!in_array($appToken->type_app_child, Constants::TYPE_APP_CHILD))
                throw new ApiException(
                    ApiException::EXCEPTION_BAD_REQUEST_400,
                    'Plz check your appToken header'
                );
            $input['app_id'] = [$appToken->app_id];
        }
        $input['app'] = $appToken->app;
        $input['type_app'] = $appToken->type_app;
        $input['type_app_child'] = $appToken->type_app_child;
        $request->replace($input);
        return $next($request);
    }


    private function safeTrim($input)
    {
        if (is_array($input)) {
            return array_map(array($this, 'safeTrim'), $input);
        }
        return trim($input);
    }

}
