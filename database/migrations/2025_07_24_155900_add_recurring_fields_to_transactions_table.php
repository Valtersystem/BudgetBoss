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
        Schema::table('transactions', function (Blueprint $table) {
            // Indica se é uma transação recorrente
            $table->boolean('is_recurring')->default(false)->after('paid');

            // Um ID para agrupar todas as transações da mesma recorrência
            $table->unsignedBigInteger('recurrence_id')->nullable()->after('is_recurring');

            // A frequência (diária, semanal, mensal, anual)
            $table->string('frequency')->nullable()->after('recurrence_id');

            // O número total de parcelas
            $table->integer('installments')->nullable()->after('frequency');

            // O número da parcela atual
            $table->integer('current_installment')->nullable()->after('installments');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn([
                'is_recurring',
                'recurrence_id',
                'frequency',
                'installments',
                'current_installment',
            ]);
        });
    }
};
