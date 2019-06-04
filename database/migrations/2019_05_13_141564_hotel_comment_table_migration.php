<?php

use App\Inside\Constants;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SupplierCommentTableMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(Constants::HOTEL_COMMENT_DB, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('hotel_id');
            $table->string('name');
            $table->longText('comment')->nullable();
            $table->string('path')->nullable();
            $table->json('info')->nullable();
            $table->timestamps();
        });
        Schema::table(Constants::HOTEL_COMMENT_DB, function (Blueprint $table) {
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
        Schema::dropIfExists(Constants::HOTEL_COMMENT_DB);
    }


}
