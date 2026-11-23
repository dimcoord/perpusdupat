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
        Schema::table('loan_receipts', function (Blueprint $table) {
            $table->string('extension_status')->nullable()->after('status'); // menunggu_persetujuan | disetujui | ditolak
            $table->unsignedTinyInteger('extension_count')->default(0)->after('extension_status');
            $table->timestamp('extension_requested_at')->nullable()->after('extension_count');
            $table->text('extension_notes')->nullable()->after('extension_requested_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loan_receipts', function (Blueprint $table) {
            $table->dropColumn(['extension_status', 'extension_count', 'extension_requested_at', 'extension_notes']);
        });
    }
};
