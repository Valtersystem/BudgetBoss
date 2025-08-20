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
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('bank_institution_id')->constrained()->onDelete('cascade');
            $table->string('name');
            // Usar decimal para valores monetários para evitar problemas de precisão
            $table->decimal('initial_balance', 10, 2)->default(0.00);
            $table->text('description')->nullable();
            $table->string('source_of_money')->nullable();
            $table->string('color')->default('#FFFFFF');
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
        Schema::dropIfExists('accounts');
    }
};
