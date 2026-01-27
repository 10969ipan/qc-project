<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('customer_claim_records', function (Blueprint $table) {
            $table->renameColumn('attachment_path', 'attachments');
        });

        Schema::table('customer_claim_records', function (Blueprint $table) {
            $table->text('attachments')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_claim_records', function (Blueprint $table) {
            $table->string('attachments', 255)->nullable()->change();
            $table->renameColumn('attachments', 'attachment_path');
        });
    }
};
