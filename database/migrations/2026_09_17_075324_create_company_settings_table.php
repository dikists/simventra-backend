<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('company_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('PT Rajawali Handal Logistik');
            $table->string('short_name')->default('Rajawali Handal');
            $table->string('tagline')->default('Logistik');
            $table->string('logo_path')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->timestamps();
        });

        // Insert initial default configuration
        DB::table('company_settings')->insert([
            'name'       => 'PT Rajawali Handal Logistik',
            'short_name' => 'Rajawali Handal',
            'tagline'    => 'Logistik',
            'logo_path'  => '/assets/logo_rhl.png',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_settings');
    }
};
