<?php

use Illuminate\Database\Seeder;

class DataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $type_app_id = 3;
        $hotel = \App\Hotel::create([
            'type_app_id' => $type_app_id,
            'title' => "هتل سان رایز کیش",
            'address' => "کیش، مقابل بازار پردیس ۲",
            'star' => 3,
            'icon' => "",
            'count_floor' => 4,
            'count_room' => 61,
            'delivery_room' => "14:00",
            'discharge_room' => "12:00",
            'desc' => "هتل سه ستاره سان رایز کیش مقابل پردیس ۲ در سال ۱۳۸۲ افتتاح و جهت ارتقا سطح کیفی خدمات در سال ۱۳۹۷ مورد بازسازی قرار گرفت. ساختمان هتل در ۴ طبقه بنا و دارای ۶۱ باب اتاق و سوئیت با امکانات رفاهی مناسب می‌باشد. این مجموعه واقع در قلب مراکز تفریحی و خرید جزیره ای با آفتاب درخشان، دریایی آبی و کرانه ای چشم نواز و در نزدیکی بازارهای پردیس ۱ و ۲ واقع گردیده است. پرسنل هتل سان رایز در تلاش اند اقامتی دلنشین را برای شما میهمانان گرامی رقم بزنند.",
            'type' => "percent",
            'percent' => 10,
            'global' => array(
                'تاریخ ساخت' => '۱۳۸۲ ( سال بازسازی ۱۳۹۷ )',
                'وضعیت دید هتل' => 'خیابان | محوطه هتل',
                'تعداد اتاق ها' => '۶۱ اتاق',
                'تعداد طبقات' => '۴ طبقه',
                'تعداد تخت ها' => '۱۶۷ تخت',
                'ظرفیت لابی' => 'با ظرفیت ۷۰ نفر',
                'وضعیت ترافیک' => 'خارج از محدوده طرح ترافیک',
            ),
            'possibilities' => array(
                'رستوران' => 'با ظرفیت ۱۰۰ نفر',
                'پارکینگ' => 'ندارد',
                'اینترنت در لابی' => 'رایگان (نامحدود)',
                'استخر' => 'ندارد',
            ),
            'terms_of_use' => array(
                'نرخ میهمان خارجی (غیرعرب زبان)' => 'یکسان با نرخنامه',
                'نرخ میهمان خارجی (عرب زبان)' => 'یکسان با نرخنامه',
                'بازه سنی اقامت رایگان کودکان' => 'کودک زیر ۵ سال (درصورت عدم استفاده از سرویس)',
                'بازه سنی برای اقامت با هزینه نیم بها' => 'ندارد',
                'پذیریش خانم مجرد' => 'با مدارک شناسایی معتبر',
                'قوانین صیغه نامه' => 'با مهر برجسته محضر',
            ),
            'lat' => 26.535363,
            'long' => 54.0181801
        ]);
        \App\HotelGallery::create([
            'type_app_id' => $type_app_id,
            'hotel_id' => $hotel->id,
            'path' => "01.jpg",
            'mime_type' => "image/jpg",
            'created_at' => date('Y-m-d')
        ]);
        \App\HotelGallery::create([
            'type_app_id' => $type_app_id,
            'hotel_id' => $hotel->id,
            'path' => "02.jpg",
            'mime_type' => "image/jpg",
            'created_at' => date('Y-m-d')
        ]);
        \App\HotelGallery::create([
            'type_app_id' => $type_app_id,
            'hotel_id' => $hotel->id,
            'path' => "03.jpg",
            'mime_type' => "image/jpg",
            'created_at' => date('Y-m-d')
        ]);
        \App\HotelGallery::create([
            'type_app_id' => $type_app_id,
            'hotel_id' => $hotel->id,
            'path' => "04.jpg",
            'mime_type' => "image/jpg",
            'created_at' => date('Y-m-d')
        ]);
        $tools = [
            "خدمات تور", "آتلیه", " آژانس مسافرتی", "لابی",
            "آسانسور", "صندوق امانات", "نمازخانه", "فتوکپی",
            "فکس", "پرینتر", "چایخانه سنتی", "کافی شاپ",
            "تاکسی سرویس", "اتاق چمدان", "فضای سبز", " خدمات تهیه بلیط",
            " سیستم تهویه مطبوع", " پذیرش ۲۴ ساعته", " دستگاه واکس کفش", "خدمات تور",
            " کرایه اتومبیل (بدون راننده)", " لاندری (خشکشویی) ",
            " پله اضطراری", " زنگ هشدار",
            " تلفن در لابی", " خدمات خانه داری", " سیستم اعلام حریق",
            " کپسول آتش نشانی", " اینترنت در لابی "
        ];
        foreach ($tools as $tool) {
            \App\HotelTools::create([
                'type_app_id' => $type_app_id,
                'hotel_id' => $hotel->id,
                'icon' => "check.svg",
                'title' => $tool,
                'created_at' => date('Y-m-d')
            ]);
        }
        \App\Room::create([
            'type_app_id' => $type_app_id,
            'hotel_id' => $hotel->id,
            'title' => "اتاق دو تخته دبل",
            'bed' => "۱ تخت دبل",
            'capacity' => 2,
            'count' => 1,
            'percent' => 10000,
            'price' => 200000,
        ]);
        \App\Room::create([
            'type_app_id' => $type_app_id,
            'hotel_id' => $hotel->id,
            'title' => "اتاق دو تخته توئین",
            'bed' => "۲ تخت سینگل",
            'capacity' => 2,
            'count' => 1,
            'percent' => 10000,
            'price' => 200000,
        ]);
        \App\Room::create([
            'type_app_id' => $type_app_id,
            'hotel_id' => $hotel->id,
            'title' => "سوئیت یکخوابه دو نفره",
            'bed' => "دبل یا سینگل",
            'capacity' => 2,
            'count' => 1,
            'percent' => 15000,
            'price' => 250000,
        ]);
        \App\Room::create([
            'type_app_id' => $type_app_id,
            'hotel_id' => $hotel->id,
            'title' => "اتاق دو تخته دبل رویال",
            'bed' => " ۱ تخت دبل",
            'capacity' => 2,
            'count' => 1,
            'percent' => 0,
            'price' => 0,
        ]);
        \App\Room::create([
            'type_app_id' => $type_app_id,
            'hotel_id' => $hotel->id,
            'title' => "سوئیت یکخوابه سه نفره",
            'bed' => "۱ تخت سینگل و ۱ تخت دبل",
            'capacity' => 3,
            'count' => 1,
            'percent' => 10000,
            'price' => 310000,
        ]);
        \App\Room::create([
            'type_app_id' => $type_app_id,
            'hotel_id' => $hotel->id,
            'title' => "اتاق سه تخته",
            'bed' => "۱ تخت سینگل و ۱ تخت دبل",
            'capacity' => 3,
            'count' => 1,
            'percent' => 75000,
            'price' => 400000,
        ]);
        \App\Room::create([
            'type_app_id' => $type_app_id,
            'hotel_id' => $hotel->id,
            'title' => "سوئیت یکخوابه چهار نفره",
            'bed' => "۲ تخت سینگل و ۱ تخت دبل",
            'capacity' => 5,
            'count' => 1,
            'percent' => 40000,
            'price' => 400000,
        ]);
        $roomTools = [
            "پاورسوئیچ", " تلفن در اتاق ", " روم سرویس", "یخچال", "مبلمان",
            "حمام", "صبحانه", "دراور", "آباژور", " کمد لباس", "تلویزیون",
            " اینترنت در اتاق ", " امکان شارژ وسایل الکترونیکی",
            " سیستم گرمایش و سرمایش ", " نوع قفل درب اتاق", " سیستم اعلام حریق",
            " صندوق امانات داخل اتاق", " مینی بار با هزینه", " سیستم تهویه مطبوع دراتاق",
            " سیستم اطفاء حریق دراتاق", " تلویزیون در اتاق ", " لوازم بهداشتی ",
            "دمپایی", " سرویس بهداشتی فرنگی در اتاق"
        ];
        for ($i = 1; $i <= 7; $i++)
            foreach ($roomTools as $tool) {
                \App\RoomTools::create([
                    'type_app_id' => $type_app_id,
                    'room_id' => $i,
                    'icon' => "check.svg",
                    'title' => $tool,
                    'created_at' => date('Y-m-d')
                ]);
            }


    }
}
