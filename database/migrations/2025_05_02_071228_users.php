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
            $table->string('email', 50)->unique();
            $table->string('password');
            $table->boolean('is_admin')->default(false);

            $table->double('latitude')->nullable();
            $table->double('longitude')->nullable();
            $table->datetime('last_update')->nullable();
            $table->datetime('last_connexion')->nullable();
            $table->datetime('date_last_position')->nullable();
            $table->datetime('last_activity')->nullable();
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
