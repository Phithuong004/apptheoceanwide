<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            // Thêm default cho total nếu chưa có
            if (Schema::hasColumn('invoice_items', 'total')) {
                $table->decimal('total', 15, 2)->default(0)->change();
            } else {
                $table->decimal('total', 15, 2)->default(0)->after('unit_price');
            }
        });
    }

    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropColumn('total');
        });
    }
};
