<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClaimsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('claims', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('claimable_type')->index();
            $table->unsignedBigInteger('claimable_id')->index();
            $table->string('remote_id')->nullable();
            $table->string('token')->unique()->index();
            $table->unsignedInteger('coin_reward');
            $table->boolean('accepted')->default(false)->index();
            $table->timestamp('claimed_at')->nullable()->default(null);
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
        Schema::dropIfExists('claims');
    }
}
