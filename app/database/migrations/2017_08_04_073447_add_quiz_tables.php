<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddQuizTables extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::create('learning_category', function($t) {
            $t->increments('id');
            $t->string('category');
            $t->integer('is_active')->default(1);
            $t->string('image_url')->nullable();
            $t->timestamps();
            $t->timestamp('deleted_at')->nullable();
        });

        Schema::create('learning_section', function($t) {
            $t->increments('id');
            $t->integer('category_id');
            $t->string('section_title');
            $t->string('section_description')->nullable();
            $t->integer('is_active')->default(1);
            $t->string('image_url')->nullable();
            $t->timestamps();
            $t->timestamp('deleted_at')->nullable();
        });

        Schema::create('learning_content', function($t) {
            $t->increments('id');
            $t->integer('category_id');
            $t->integer('section_id');
            $t->integer('quiz_id')->nullable();
            $t->string('content');
            $t->string('content_description')->nullable();
            $t->longText('content_details_json')->nullable();
            $t->string('image_url')->nullable();
            $t->integer('is_active')->default(1);
            $t->timestamps();
            $t->timestamp('deleted_at')->nullable();
        });

        Schema::create('learning_quiz', function($t) {
            $t->increments('id');
            $t->string('quiz_name');
            $t->integer('is_active')->default(1);
            $t->timestamps();
            $t->timestamp('deleted_at')->nullable();
        });

        Schema::create('learning_quiz_questions', function($t) {
            $t->increments('id');
            $t->integer('quiz_id');
            $t->string('title');
            $t->integer('is_active')->default(1);
            $t->timestamps();
            $t->timestamp('deleted_at')->nullable();
        });

        Schema::create('learning_quiz_question_answers', function($t) {
            $t->increments('id');
            $t->integer('question_id');
            $t->string('answer');
            $t->integer('is_answer')->default(0);
            $t->timestamps();
        });

        Schema::create('learning_quiz_results', function($t) {
            $t->increments('id');
            $t->integer('walker_id');
            $t->integer('quiz_id');
            $t->integer('score');
            $t->string('result');
            $t->string('completed_duration');
            $t->timestamps();
            $t->timestamp('deleted_at')->nullable();
        });

        Schema::create('learning_quiz_participant_answers', function($t) {
            $t->increments('id');
            $t->integer('walker_id');
            $t->integer('quiz_id');
            $t->integer('question_id');
            $t->integer('learning_quiz_question_answers_id');
            $t->timestamps();
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
	}

}
