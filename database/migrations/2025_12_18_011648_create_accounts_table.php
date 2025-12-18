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
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name'); // User-friendly label
            $table->string('email')->unique();
            $table->string('imap_host');
            $table->integer('imap_port')->default(993);
            $table->string('imap_encryption')->default('ssl');
            $table->text('imap_password'); // Encrypted
            $table->string('smtp_host')->nullable();
            $table->integer('smtp_port')->nullable();
            $table->text('smtp_password')->nullable(); // Encrypted
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable(); // Account-specific AI config
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
