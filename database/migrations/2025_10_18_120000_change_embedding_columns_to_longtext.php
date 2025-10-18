<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeEmbeddingColumnsToLongtext extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ai_knowledge', function (Blueprint $table) {
            $table->longText('embedding')->nullable()->change();
        });

        Schema::table('jobs', function (Blueprint $table) {
            $table->longText('embedding')->nullable()->change();
        });

        Schema::table('news', function (Blueprint $table) {
            $table->longText('embedding')->nullable()->change();
        });

        Schema::table('trainings', function (Blueprint $table) {
            $table->longText('embedding')->nullable()->change();
        });

        Schema::table('ai_documents', function (Blueprint $table) {
            $table->longText('embedding')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ai_knowledge', function (Blueprint $table) {
            $table->text('embedding')->nullable()->change();
        });

        Schema::table('jobs', function (Blueprint $table) {
            $table->text('embedding')->nullable()->change();
        });

        Schema::table('news', function (Blueprint $table) {
            $table->text('embedding')->nullable()->change();
        });

        Schema::table('trainings', function (Blueprint $table) {
            $table->text('embedding')->nullable()->change();
        });

        Schema::table('ai_documents', function (Blueprint $table) {
            $table->text('embedding')->nullable()->change();
        });
    }
}
