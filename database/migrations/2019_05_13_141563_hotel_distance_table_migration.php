<?php

use App\Inside\Constants;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class HotelDistanceTableMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(Constants::HOTEL_DISTANCE_DB, function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('app_id');
            $table->bigInteger('hotel_id');
            $table->string('title');
            $table->string('link')->nullable();
            $table->string('distance');
            $table->timestamp('created_at');
        });
        Schema::table(Constants::HOTEL_DISTANCE_DB, function (Blueprint $table) {
            $table->foreign('hotel_id')->references('id')->on(Constants::HOTEL_DB)->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(Constants::HOTEL_DISTANCE_DB);
    }
}
