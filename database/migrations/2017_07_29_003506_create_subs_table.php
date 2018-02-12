<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSubsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('sub_id', 36)->unique();
            $table->string('owner')->nullable();
            $table->string('sub_domain', 100)->unique();
            $table->string('domain', 100);
            $table->string('status');
            $table->string('provider');
            $table->string('tags');
            $table->string('www', '100')->nullable();
            $table->string('host')->nullable();
            $table->tinyInteger('isActive');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subs');
    }
}
