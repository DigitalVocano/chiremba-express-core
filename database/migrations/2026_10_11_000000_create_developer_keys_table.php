<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('developer_keys')) {
            Schema::create('developer_keys', function (Blueprint $table) {
                $table->id();
                $table->string('app_name');
                $table->string('developer_name');
                $table->string('developer_email');
                $table->string('api_key', 64)->unique();
                $table->string('secret_key', 128);
                $table->text('allowed_domains')->nullable();
                $table->integer('rate_limit_per_minute')->default(60);
                $table->boolean('is_active')->default(true);
                $table->timestamp('last_used_at')->nullable();
                $table->timestamps();

                $table->index('developer_email');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('developer_keys');
    }
};
