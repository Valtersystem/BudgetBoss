<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->foreignId('financial_institution_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('account_type_id')->nullable()->constrained()->onDelete('set null');
        });
    }


    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->string('type');

            $table->dropForeign(['financial_institution_id']);
            $table->dropForeign(['account_type_id']);
            $table->dropColumn(['financial_institution_id', 'account_type_id']);
        });
    }
};
