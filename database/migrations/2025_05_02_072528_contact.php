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
        Schema::create('contact', function (Blueprint $table) {
            $table->id();
            $table->integer('id_recepteur');
            $table->integer('id_emetteur');
            $table->timestamps();
        });
        Schema::table('contact', function (Blueprint $table) {
            $table->foreign('id_recepteur')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('id_emetteur')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::dropIfExists('contact');
    }
};
