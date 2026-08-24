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
        Schema::create(
            'charge_payment_details',
            function (Blueprint $table) {
                $table->id();

                $table->foreignId('charge_id')
                    ->unique()
                    ->constrained('charges')
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();

                $table->string('barcode', 100)
                    ->nullable();

                $table->string('pix_key', 255)
                    ->nullable();

                $table->string('card_token', 255)
                    ->nullable();

                $table->string('card_brand', 30)
                    ->nullable();

                $table->string('card_last_four', 4)
                    ->nullable();

                $table->unsignedTinyInteger('card_exp_month')
                    ->nullable();

                $table->unsignedSmallInteger('card_exp_year')
                    ->nullable();

                $table->timestamps();
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('charge_payment_details');
    }
};
