<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEmbeddingToAllTables extends Migration
{
    public function up()
    {
        if (Schema::hasTable('news')) {
            Schema::table('news', function (Blueprint $table) {
                if (!Schema::hasColumn('news', 'embedding')) {
                    $table->text('embedding')->nullable();
                }
            });
        }

        if (Schema::hasTable('trainings')) {
            Schema::table('trainings', function (Blueprint $table) {
                if (!Schema::hasColumn('trainings', 'embedding')) {
                    $table->text('embedding')->nullable();
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('news') && Schema::hasColumn('news', 'embedding')) {
            Schema::table('news', function (Blueprint $table) {
                $table->dropColumn('embedding');
            });
        }

        if (Schema::hasTable('trainings') && Schema::hasColumn('trainings', 'embedding')) {
            Schema::table('trainings', function (Blueprint $table) {
                $table->dropColumn('embedding');
            });
        }
    }
}
