<?php

use App\Inside\Constants;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RoomGalleryTableMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(Constants::ROOM_GALLERY_DB, function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('type_app_id');
            $table->bigInteger('room_id');
            $table->string('path');
            $table->string('mime_type')->nullable();
            $table->timestamp('created_at');
        });
        Schema::table(Constants::ROOM_GALLERY_DB, function (Blueprint $table) {
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
        Schema::dropIfExists(Constants::ROOM_GALLERY_DB);
    }
}
