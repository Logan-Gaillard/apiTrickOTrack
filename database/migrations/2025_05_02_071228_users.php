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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nickname', 25)->unique();
            $table->string('nom', 30);
            $table->string('prenom', 30);
            $table->string('email', 50)->unique();
            $table->string('password');
            $table->integer('latitude')->nullable();
            $table->integer('longitude')->nullable();
            $table->datetime('last_update')->nullable();
            $table->datetime('last_connexion')->nullable();

            $table->string('api_token', 80)->unique()->nullable()->default(null);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::dropIfExists('users');
    }
};
