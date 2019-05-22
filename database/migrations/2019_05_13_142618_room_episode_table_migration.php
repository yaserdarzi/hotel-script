<?php

use App\Inside\Constants;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RoomEpisodeTableMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(Constants::ROOM_EPISODE_DB, function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('app_id');
            $table->bigInteger('hotel_id');
            $table->bigInteger('room_id');
            $table->bigInteger('supplier_id');
            $table->bigInteger('capacity')->default(1);
            $table->bigInteger('capacity_filled')->default(0);
            $table->bigInteger('price')->default(0);
            $table->string('type_percent')->default(Constants::TYPE_PERCENT_PERCENT);
            $table->bigInteger('percent')->default(0);
            $table->timestamp('date');
            $table->string('status')->default(Constants::STATUS_ACTIVE);
            $table->timestamps();
        });
        Schema::table(Constants::ROOM_EPISODE_DB, function (Blueprint $table) {
            $table->foreign('hotel_id')->references('id')->on(Constants::HOTEL_DB)->onDelete('cascade');
            $table->foreign('room_id')->references('id')->on(Constants::ROOM_DB)->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(Constants::ROOM_EPISODE_DB);
    }
}
