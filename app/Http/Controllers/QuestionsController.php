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
        $question->save();
    
        $answer = new Answers();
        $answer->answers = $request->input('answer');
        $answer->question_id = $question->id;
        $answer->save();
    
        return redirect()->back()->with('success', 'Question and answer saved successfully.');
    }
}
