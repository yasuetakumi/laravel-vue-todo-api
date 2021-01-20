<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDummyMeetingsTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('dummy_meetings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title', 255);
            $table->bigInteger('customer');
            $table->date('meeting_date');
            $table->bigInteger('attendee');
            $table->string('location_image_url', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('dummy_meetings');
    }
}
