<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostcodesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('postcodes', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->string('postcode', 7);
			$table->string('prefecture_kana', 100);
			$table->string('city_kana', 100);
			$table->string('local_kana', 100)->nullable();
			$table->string('prefecture', 100);
			$table->string('city', 100);
			$table->string('local', 100)->nullable();
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
		Schema::drop('postcodes');
	}

}
