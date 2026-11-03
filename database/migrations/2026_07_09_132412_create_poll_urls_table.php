<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePollUrlsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('poll_urls', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->text('poll_url');
            $table->decimal('amount', 10, 2);
            $table->unsignedBigInteger('user_id');
            $table->integer('role_id'); // 1 = Patient, 2 = Doctor, 3 = Pharmacy
            $table->string('status')->default('pending'); // pending, completed, failed
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('poll_urls');
    }
}
