<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_number')->nullable()->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('loan_date');
            $table->date('due_date');
            $table->string('status')->default('menunggu_persetujuan'); // menunggu_persetujuan | disetujui
            $table->timestamps();
        });

        Schema::table('loans', function (Blueprint $table) {
            $table->foreignId('loan_receipt_id')
                ->nullable()
                ->after('book_id')
                ->constrained('loan_receipts')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropConstrainedForeignId('loan_receipt_id');
        });

        Schema::dropIfExists('loan_receipts');
    }
};
