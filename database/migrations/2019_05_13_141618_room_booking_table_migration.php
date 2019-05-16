<?php

use App\Inside\Constants;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RoomBookingTableMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(Constants::ROOM_BOOKING_DB, function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('type_app_id');
            $table->bigInteger('room_id');
            $table->bigInteger('user_id');
            $table->bigInteger('invoice_id');
            $table->string('ticket_number');
            $table->timestamp('date');
            $table->timestamp('created_at');
        });
        Schema::table(Constants::ROOM_BOOKING_DB, function (Blueprint $table) {
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
        Schema::dropIfExists(Constants::ROOM_BOOKING_DB);
    }
}
