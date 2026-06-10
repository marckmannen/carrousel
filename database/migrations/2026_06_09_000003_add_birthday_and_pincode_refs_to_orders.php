<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Add foreign keys to new tables
            $table->foreignId('birthday_id')->nullable()->after('birthdate')->constrained('birthdates')->nullOnDelete();
            $table->foreignId('pincode_id')->nullable()->after('pincode')->constrained('pincodes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['birthday_id']);
            $table->dropForeign(['pincode_id']);
            $table->dropColumn(['birthday_id', 'pincode_id']);
        });
    }
};
