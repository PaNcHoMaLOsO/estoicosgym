<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('estados', function (Blueprint $table) {
            $table->string('color', 20)->default('secondary')->comment('Color Bootstrap: primary, success, danger, warning, info, secondary');
        });
    }

    public function down(): void
    {
        Schema::table('estados', function (Blueprint $table) {
            $table->dropColumn('color');
        });
    }
};
