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
        Schema::create('avis', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('status')->default(0);
            $table->string('commentaire', 255)->nullable();
            $table->timestamps();
        });
        Schema::table('avis', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
        });
        Schema::table('avis', function (Blueprint $table) {
            $table->foreignId('place_id')->constrained('places')->onDelete('cascade')->nullable();
        });
        Schema::table('avis', function (Blueprint $table) {
            $table->foreignId('alert_id')->constrained('alerts')->onDelete('cascade')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::dropIfExists('avis');
    }
};
