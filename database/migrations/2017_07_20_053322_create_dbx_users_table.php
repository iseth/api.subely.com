<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDbxUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dbx_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('uid', 36)->unique();
            $table->string('dbid', 50)->unique();
            $table->string('email')->unique();
            $table->string('display_name', '60')->nullable();
            $table->string('firstName', '100')->nullable();
            $table->string('lastName', '100')->nullable();
            $table->string('profile_pic_url', '50')->nullable();
            $table->boolean('verified')->nullable();
            $table->string('country')->nullable();
            $table->string('language')->nullable();
            $table->string('type')->nullable();
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
        Schema::dropIfExists('dbx_users');
    }
}
