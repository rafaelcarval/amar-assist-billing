<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('customer_id')
                ->constrained('customers')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->string('type', 2);

            $table->unsignedTinyInteger('billing_cycle_day');

            $table->boolean('active')
                ->default(true)
                ->index();

            $table->timestamps();

            $table->index([
                'customer_id',
                'active',
            ]);
        });

        DB::statement(
            'ALTER TABLE contracts
            ADD CONSTRAINT chk_billing_cycle_day
            CHECK (billing_cycle_day BETWEEN 1 AND 31)'
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contracts');
    }
};
