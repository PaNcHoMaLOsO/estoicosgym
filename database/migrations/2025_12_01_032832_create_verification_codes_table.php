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
        Schema::create('verification_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('code', 6); // Código de 6 dígitos
            $table->enum('type', ['login', 'password_reset', 'phone_verify'])->default('login');
            $table->enum('channel', ['sms', 'whatsapp', 'email'])->default('whatsapp');
            $table->string('phone', 20)->nullable(); // Teléfono destino
            $table->boolean('is_used')->default(false);
            $table->timestamp('expires_at');
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            
            // Índices
            $table->index(['user_id', 'type', 'is_used']);
            $table->index('code');
            $table->index('expires_at');
        });
        
        // Agregar campo de teléfono a users si no existe
        if (!Schema::hasColumn('users', 'phone')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('phone', 20)->nullable()->after('email');
                $table->boolean('two_factor_enabled')->default(false)->after('phone');
                $table->enum('two_factor_channel', ['sms', 'whatsapp'])->default('whatsapp')->after('two_factor_enabled');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verification_codes');
        
        if (Schema::hasColumn('users', 'phone')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn(['phone', 'two_factor_enabled', 'two_factor_channel']);
            });
        }
    }
};
