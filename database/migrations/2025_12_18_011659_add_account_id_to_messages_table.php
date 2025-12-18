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
        Schema::table('messages', function (Blueprint $table) {
            $table->foreignId('account_id')->after('id')->nullable()->constrained()->onDelete('cascade');
            $table->index(['account_id', 'mailbox_folder']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['account_id']);
            $table->dropIndex(['account_id', 'mailbox_folder']);
            $table->dropColumn('account_id');
        });
    }
};
