<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
        //
            $table->text('nik')
            ->after('email')
            ->nullable();

            $table->text('position')
            ->after('nik')
            ->nullable();

            $table->text('rank')
            ->after('position')
            ->nullable();

            $table->text('nrp')
            ->after('rank')
            ->nullable();

            $table->text('phone')
            ->after('nrp')
            ->nullable();
        });



    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('nik');
            $table->dropColumn('nik');
            $table->dropColumn('position');
            $table->dropColumn('rank');
            $table->dropColumn('nrp');
            $table->dropColumn('phone');
        });
    }
}
