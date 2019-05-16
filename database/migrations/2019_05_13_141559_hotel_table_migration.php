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
