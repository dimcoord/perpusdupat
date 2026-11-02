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
        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn('fine_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            // Sesuaikan tipe data ini dengan tipe data awal fine_amount Anda (misal: integer atau decimal)
            $table->decimal('fine_amount', 10, 2)->default(0)->nullable(); 
        });
    }
};