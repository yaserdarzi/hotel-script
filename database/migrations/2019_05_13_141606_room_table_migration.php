<?php

use App\Inside\Constants;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RoomTableMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(Constants::ROOM_DB, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('app_id');
            $table->bigInteger('hotel_id');
            $table->string('title');
            $table->string('image')->nullable();
            $table->longText('desc')->nullable();
            $table->bigInteger('capacity');
            $table->string('bed')->nullable();
            $table->boolean('is_breakfast')->default(false);
            $table->boolean('is_lunch')->default(false);
            $table->boolean('is_dinner')->default(false);
            $table->integer('sort')->default(1);
            $table->timestamps();
            $table->softDeletes();

        });
        Schema::table(Constants::ROOM_DB, function (Blueprint $table) {
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
        Schema::dropIfExists(Constants::ROOM_DB);
    }
}
