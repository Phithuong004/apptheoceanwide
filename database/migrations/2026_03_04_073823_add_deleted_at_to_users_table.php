<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Thêm các cột còn thiếu nếu chưa có
            if (!Schema::hasColumn('users', 'deleted_at')) {
                $table->softDeletes();
            }
            if (!Schema::hasColumn('users', 'avatar')) {
                $table->string('avatar')->nullable()->after('name');
            }
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable()->after('avatar');
            }
            if (!Schema::hasColumn('users', 'timezone')) {
                $table->string('timezone')->default('Asia/Ho_Chi_Minh')->after('phone');
            }
            if (!Schema::hasColumn('users', 'locale')) {
                $table->string('locale')->default('vi')->after('timezone');
            }
            if (!Schema::hasColumn('users', 'status')) {
                $table->enum('status', ['active','inactive','banned'])->default('active')->after('locale');
            }
            if (!Schema::hasColumn('users', 'last_active_at')) {
                $table->timestamp('last_active_at')->nullable()->after('status');
            }
            if (!Schema::hasColumn('users', 'two_factor_secret')) {
                $table->string('two_factor_secret')->nullable()->after('last_active_at');
            }
            if (!Schema::hasColumn('users', 'two_factor_enabled')) {
                $table->boolean('two_factor_enabled')->default(false)->after('two_factor_secret');
            }
            if (!Schema::hasColumn('users', 'preferences')) {
                $table->text('preferences')->nullable()->after('two_factor_enabled');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn([
                'avatar','phone','timezone','locale','status',
                'last_active_at','two_factor_secret','two_factor_enabled','preferences',
            ]);
        });
    }
};
