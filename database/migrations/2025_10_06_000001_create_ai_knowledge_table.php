<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAiKnowledgeTable extends Migration
{
    public function up()
    {
        Schema::create('ai_knowledge', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('category', 50)->index(); // 'contact', 'faq', 'service', 'about'
            $table->string('key', 100)->index(); // 'phone', 'email', 'working_hours', etc.
            $table->text('value');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(0); // Muhimlik darajasi
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ai_knowledge');
    }
}
