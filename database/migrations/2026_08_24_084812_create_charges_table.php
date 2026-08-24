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
    public function up(): void
    {
        Schema::create('charges', function (Blueprint $table) {
            $table->id();

            $table->foreignId('contract_id')
                ->constrained('contracts')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->string('payment_method', 20);

            $table->decimal('base_amount', 15, 2);

            $table->decimal('late_fee_amount', 15, 2)
                ->default(0);

            $table->decimal('total_amount', 15, 2);

            $table->date('due_date');

            $table->string('status', 20)
                ->default('OPEN');

            $table->timestamp('paid_at')
                ->nullable();

            $table->timestamps();

            $table->index('status');
            $table->index('due_date');

            $table->index([
                'status',
                'due_date',
            ]);

            $table->index([
                'contract_id',
                'status',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('charges');
    }
};
