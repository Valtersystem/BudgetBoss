<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('account_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('tag_id')->nullable()->constrained()->onDelete('set null');

            $table->enum('type', ['income', 'expense']); // Receita ou Despesa
            $table->decimal('value', 10, 2);
            $table->boolean('is_paid')->default(true);
            $table->date('date');
            $table->string('description');
            $table->text('notes')->nullable();

            // Campos para recorrência
            $table->enum('repetition_type', ['fixed', 'custom'])->nullable();
            $table->integer('repetition_interval_value')->nullable();
            $table->enum('repetition_interval_unit', ['day', 'week', 'month', 'year'])->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transactions');
    }
};
