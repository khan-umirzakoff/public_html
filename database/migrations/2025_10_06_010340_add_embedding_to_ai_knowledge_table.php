<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEmbeddingToAiKnowledgeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ai_knowledge', function (Blueprint $table) {
            if (!Schema::hasColumn('ai_knowledge', 'embedding')) {
                $table->text('embedding')->nullable();
            }
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
            if (Schema::hasColumn('ai_knowledge', 'embedding')) {
                $table->dropColumn('embedding');
            }
        });
    }
}
