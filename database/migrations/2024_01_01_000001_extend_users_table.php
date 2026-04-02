<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->string('avatar')->nullable()->after('phone');
            $table->string('referral_code')->unique()->nullable()->after('avatar');
            $table->unsignedBigInteger('referred_by')->nullable()->after('referral_code');
            $table->boolean('is_banned')->default(false)->after('referred_by');
            $table->boolean('is_admin')->default(false)->after('is_banned');
            $table->string('google2fa_secret')->nullable()->after('is_admin');
            $table->string('kyc_status')->default('none')->after('google2fa_secret'); // none|pending|approved|rejected
            $table->string('country')->nullable()->after('kyc_status');
            $table->string('timezone')->default('UTC')->after('country');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone','avatar','referral_code','referred_by','is_banned','is_admin','google2fa_secret','kyc_status','country','timezone']);
        });
    }
};
