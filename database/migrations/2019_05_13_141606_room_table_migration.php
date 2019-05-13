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
            $table->bigInteger('type_app_id');
            $table->bigInteger('hotel_id');
            $table->string('title');
            $table->string('image')->nullable();
            $table->longText('desc')->nullable();
            $table->json('service')->nullable();
            $table->bigInteger('capacity');
            $table->bigInteger('count');
            $table->bigInteger('percent')->nullable()->default(0);
            $table->bigInteger('price')->nullable()->default(0);
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
