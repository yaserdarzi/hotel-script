<?php

use App\Inside\Constants;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class HotelTableMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(Constants::HOTEL_DB, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('type_app_id');
            $table->string('title');
            $table->string('icon');
            $table->longText('desc')->nullable();
            $table->text('address')->nullable();
            $table->integer('star')->default(0);
            $table->integer('count_floor')->default(0);
            $table->integer('count_room')->default(0);
            $table->string('delivery_room')->nullable();
            $table->string('discharge_room')->nullable();
            $table->string('type');
            $table->bigInteger('percent')->nullable()->default(0);
            $table->bigInteger('price')->nullable()->default(0);
            $table->bigInteger('award')->nullable()->default(0);
            $table->json('global')->nullable();
            $table->json('possibilities')->nullable();
            $table->json('terms_of_use')->nullable();
            $table->double('lat')->default(0);
            $table->double('long')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(Constants::HOTEL_DB);
    }
}
