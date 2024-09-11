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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->string('message_identifier')->unique()->index()->nullable();
            $table->boolean('is_seen')->default(0);
            $table->boolean('is_answered')->default(0);
            $table->boolean('is_recent')->default(0);
            $table->boolean('is_flagged')->default(0);
            $table->boolean('is_deleted')->default(0);
            $table->boolean('is_draft')->default(0);

            $table->string('subject', 255)->nullable();
            $table->string('from')->nullable();
            $table->string('sender')->nullable();

            $table->json('reply_to_addresses')->nullable();
            $table->datetime('date')->nullable();
            $table->text('content')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
