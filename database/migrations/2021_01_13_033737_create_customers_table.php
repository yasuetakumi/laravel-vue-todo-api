<?php
// -----------------------------------------------------------------------------
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
// -----------------------------------------------------------------------------

// -----------------------------------------------------------------------------
class CreateCustomersTable extends Migration {
    // -------------------------------------------------------------------------
    public function up() {
        Schema::create('customers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 191);
            $table->string('email', 191)->unique();
            $table->string('phone', 191);
            $table->string('website', 191);
            $table->timestamps();
        });
    }
    // -------------------------------------------------------------------------

    // -------------------------------------------------------------------------
    public function down() {
        Schema::dropIfExists('customers');
    }
    // -------------------------------------------------------------------------
}
// -----------------------------------------------------------------------------
