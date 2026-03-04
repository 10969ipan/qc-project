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
        Schema::table('kakotoras', function (Blueprint $table) {
            // New columns in order
            $table->string('no_reg')->nullable()->after('date');
            $table->date('issue_date')->nullable()->after('no_reg');
            $table->string('rev_model')->nullable()->after('issue_date');
            $table->string('family')->nullable()->after('rev_model');
            $table->string('category_nm_mp')->nullable()->after('family');
            $table->string('category_claim')->nullable()->after('category_nm_mp');
            $table->string('mould')->nullable()->after('part_number');
            $table->string('owner_mould')->nullable()->after('mould');
            $table->string('similar_part')->nullable()->after('owner_mould');
            $table->string('section')->nullable()->after('similar_part');
            $table->string('cause')->nullable()->after('process');
            $table->string('pic')->nullable()->after('countermeasure');
            $table->string('supplier')->nullable()->after('pic');
            $table->string('defect_category')->nullable()->after('supplier');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kakotoras', function (Blueprint $table) {
            $table->dropColumn([
                'no_reg',
                'issue_date',
                'rev_model',
                'family',
                'category_nm_mp',
                'category_claim',
                'mould',
                'owner_mould',
                'similar_part',
                'section',
                'cause',
                'pic',
                'supplier',
                'defect_category'
            ]);
        });
    }
};
