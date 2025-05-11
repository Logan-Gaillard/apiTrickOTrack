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
        Schema::create('places', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_alert')->default(0);
            $table->double('latitude');
            $table->double('longitude');
            $table->string('designation')->nullable();
            $table->boolean('is_house')->default(0);
            $table->boolean('is_event')->default(0);
            $table->string('adresse')->nullable();
            $table->timestamps();
        });
        Schema::table('places', function (Blueprint $table) {
            $table->foreignId('id_user')->constrained('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::dropIfExists('places');
    }
};
