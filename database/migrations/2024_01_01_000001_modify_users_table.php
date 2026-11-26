<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'seller', 'affiliate', 'customer'])->default('customer')->after('email');
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active')->after('role');
            $table->string('phone', 20)->nullable()->after('status');
            $table->string('avatar')->nullable()->after('phone');
            $table->string('referral_code', 20)->unique()->nullable()->after('avatar');
            $table->string('locale', 5)->default('fr')->after('referral_code');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'status', 'phone', 'avatar', 'referral_code', 'locale']);
            $table->dropSoftDeletes();
        });
    }
};
