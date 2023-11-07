<?php

namespace App\Http\Controllers;

use App\Models\Answers;
use App\Models\Questions;
use Illuminate\Http\Request;

class QuestionsController extends Controller
{
    //

    public function store(Request $request)
    {
        $question = new Questions();
        $question->questions = $request->input('question');
        $question->corresponding_number = $request->input('corresponding_number');
        $question->category = $request->input('category');
        $question->sub_category = $request->input('sub_category');

        $question->save();
    
        $answer = new Answers();
        $answer->answers = $request->input('answer');
        $answer->question_id = $question->id;
        $answer->save();
    
        return redirect()->back()->with('success', 'Question and answer saved successfully.');
    }

    public function list()
    {
        $question_model = new Questions();
        $questions = $question_model->latest()->paginate(10);

        return view("questions_and_answers.list",compact("questions"));
    }

    public function edit($id)
    {
        $question_model = new Questions();
        $answer_model = new Answers();
        $answer = $answer_model->where("question_id",$id)->first();
        $question = $question_model->where("id",$id)->first();

        return view("questions_and_answers.edit",compact("question","answer"));

    }

    public function store_edit(Request $request)
    {

        $question_model = new Questions();
        $answer_model = new Answers();

        $question = $request->input('question');
        $answer = $request->input('answer');
        $category = $request->input('category');
        $sub_category = $request->input('sub_category');
        $corresponding_number = $request->input('corresponding_number');



        $q_id = $request->input('q_id');
        $a_id = $request->input('a_id');

        if (!empty($question)) {
            $question_model->where('id', $q_id)->update(['questions' => $question]);
        }


        if (!empty($category)) {
            $question_model->where('id', $q_id)->update(['category' => $category]);
        }

        if (!empty($corresponding_number)) {
            $question_model->where('id', $q_id)->update(['corresponding_number' => $corresponding_number]);
        }

        if (!empty($sub_category)) {
            $question_model->where('id', $q_id)->update(['sub_category' => $sub_category]);
        }

        if (!empty($answer)) {
            $answer_model->where('id', $a_id)->update(['answers' => $answer]);
        }

        return redirect('list-questions')->with('success', 'Question and Answer updated successfully!');

    }
}
