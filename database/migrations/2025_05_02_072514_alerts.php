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
        //
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->string('message', 255)->nullable();
            $table->boolean('is_celebrated')->default(false);
            $table->boolean('is_decorated')->default(false);
            $table->date('expiration_date')->nullable()->default(date('Y-m-d', strtotime('+1 day')));
            $table->timestamps();
        });
        Schema::table('alerts', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
        });
        Schema::table('alerts', function (Blueprint $table) {
            $table->foreignId('place_id')->constrained('places')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::dropIfExists('alerts');
    }
};
