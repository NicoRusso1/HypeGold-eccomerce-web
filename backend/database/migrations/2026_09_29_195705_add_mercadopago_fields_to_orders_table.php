<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('mp_preference_id')->nullable()->after('discount');
            $table->string('mp_payment_id')->nullable()->unique()->after('mp_preference_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['mp_preference_id', 'mp_payment_id']);
        });
    }
};
