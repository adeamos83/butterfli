<?php
class LearningCenterController extends \BaseController
{
    /**
     * Display a listing of the resource.
     *
     *
     * @return Response
     */
    public function __construct()
    {
        if (Config::get('app.production')) {
            echo "Something cool is going to be here soon.";
            die();
        }
        $this->beforeFilter(function () {
            if (Session::has('walker_id')) {
                $walker_id = Session::get('walker_id');
                $walker = Walker::find($walker_id);
                Session::put('is_approved', $walker->is_approved);
                Session::put('walker_name', $walker->contact_name);
                Session::put('walker_pic', $walker->picture);
            } elseif (Session::has('admin_id')) {
                $admin_id = Session::get('admin_id');
            } else {
                return Redirect::to('/provider/signin');
            }
        }, array('except' => array(
            'providerLogin',
            'providerVerify',
            'providerForgotPassword',
            'providerRegister',
            'providerSave',
            'providerActivation',
            'surroundingCars',
        )));
    }
    public function index()
    {
        return Redirect::to('/provider/signin');
    }
    public function DriverLearningCenter()
    {
        $learningCategory = LearningCategory::where('is_active', '=', 1)->get();
        return View::make('learningcenter.providerLearning')
            ->with('title', 'Driver Learning Center')
            ->with('page', 'learning')
            ->with('learningCategory', $learningCategory);
    }
    public function LearningStep1(){
        $Walker = Walker::where('id', '=', Session::get('walker_id'))->first();
        $walker_name = $Walker->contact_name;
        return View::make('learningcenter.providerLearningStep1')
            ->with('title', '')
            ->with('page', 'learningstart')
            ->with('walker_name',$walker_name);
    }
    public function Sections()
    {
        $category_id = Request::segment(3);
        $walker_id = Session::get('walker_id');
        $learningCategory = LearningCategory::where('id', '=', $category_id)->first();
        $learningSections = LearningSection::where('category_id', '=', $category_id)
            ->where('deleted_at', '=', NULL)->get();

        $learningSectionsArray = array();
        $tSectionsArray = array();
        foreach($learningSections as $section){

            $ContentCount  = LearningContent::where('category_id', '=', $category_id)
                ->where('section_id', '=', $section->id)
                ->where('deleted_at', '=', NULL)->count();

            $passedContent = DB::table('learning_content')
                ->join('learning_quiz_results', function($q) use ($walker_id) {
                    $q->on('learning_quiz_results.content_id', '=', 'learning_content.id');
                    $q->where('learning_quiz_results.walker_id', '=', $walker_id);
                    $q->where('learning_quiz_results.result', '=', "Pass");
                })
                ->select('learning_content.id','learning_quiz_results.result')
                ->where('learning_content.section_id', '=', $section->id)
                ->where('learning_quiz_results.deleted_at', '=', NULL)
                ->where('learning_content.deleted_at', '=', NULL)->count();

            if ($ContentCount > 0 && $ContentCount == $passedContent){
                $tSectionsArray['section']['data'] = $section;
                $tSectionsArray['section']['completed'] = "true";
            } else{
                $tSectionsArray['section']['data'] = $section;
                $tSectionsArray['section']['completed'] = "false";
            }

            array_push($learningSectionsArray, $tSectionsArray);
        }
        //echo "<pre>";
        //print_r($learningSectionsArray);
        // echo "<pre>";
        ////print_r($learningContent);
        //die();

        Session::put('category_id', $category_id);
        if (count($learningSections) <= 0 && !Session::has('admin_id')) {
            return Redirect::to('learningcenter/moduleerror');
        }

        return View::make('learningcenter.section')
            ->with('title', $learningCategory->category)
            ->with('page', 'learning')
            ->with('learningSections', $learningSections)
            ->with('learningCategory', $learningCategory)
            ->with('learningSectionsArray', $learningSectionsArray);
    }
    public function Contents()
    {
        $category_id = Request::segment(3);
        $section_id = Request::segment(4);
        Session::put('section_id', $section_id);
        $walker_id = Session::get('walker_id');
        $learningSections = LearningSection::where('id', '=', $section_id)->first();

        /*$learningContent = LearningContent::where('category_id', '=', $category_id)
            ->where('section_id', '=', $section_id)
            ->where('deleted_at', '=', NULL)->get();*/

        $learningContent = DB::table('learning_content')
            ->leftJoin('learning_quiz_results', function($q) use ($walker_id) {
                $q->on('learning_quiz_results.content_id', '=', 'learning_content.id');
                $q->where('learning_quiz_results.walker_id', '=', $walker_id);
            })
            ->select('learning_content.*','learning_quiz_results.result')
            ->orderBy('learning_content.id', 'ASC')
            ->where('learning_content.section_id', '=', $section_id)
            ->where('learning_quiz_results.deleted_at', '=', NULL)
            ->where('learning_content.deleted_at', '=', NULL)->get();


        //$learningQuiz = LearningQuiz::where('is_active', '=', 1)->where('deleted_at', '=', NULL)->get();

        $learningQuiz = DB::table('learning_quiz')
            ->join('learning_quiz_questions', function($q) {
            $q->on('learning_quiz_questions.quiz_id', '=', 'learning_quiz.id');
            $q->where('learning_quiz_questions.is_active', '=', 1);
            })
            ->select('learning_quiz_questions.quiz_id','learning_quiz.*')->distinct()
            ->where('learning_quiz.is_active', '=', 1)
            ->where('learning_quiz.deleted_at', '=', NULL)->get();

        return View::make('learningcenter.content')
            ->with('title', 'Content')
            ->with('page', 'learning')
            ->with('learningSections', $learningSections)
            ->with('learningContent', $learningContent)
            ->with('learningQuiz', $learningQuiz)
            ->with('back_category_id',$category_id);
    }
    public function ContentDetails()
    {
        $category_id = Request::segment(3);
        $section_id = Request::segment(4);
        $content_id = Request::segment(5);
        $learningContent = LearningContent::where('id', '=', $content_id)
            ->where('category_id', '=', $category_id)->where('section_id', '=', $section_id)->first();
        Log::info('$content_id = ' . print_r($content_id, true));
        Log::info('$section_id = ' . print_r($section_id, true));
        Log::info('$category_id = ' . print_r($category_id, true));
        if ($learningContent->content_details_json != '' && $learningContent->content_details_json != null) {
            //$content_details_json = json_decode(file_get_contents($learningContent->content_details_json), true);
            $content_details_json = json_decode($learningContent->content_details_json, true);
        } else {
            $content_details_json = array();
        }
        $walker_id = Session::get('walker_id');
        $walker = Walker::find($walker_id);
        $learningQuizResult = LearningQuizResults::where('content_id', '=', $content_id)->where('walker_id', '=', $walker_id)->orderBy('created_at', 'desc')->first();
//        if ($learningContent == '' && !Session::has('admin_id')) {
//            return Redirect::to('learningcenter/learning');
//        }

        if (Session::has('admin_id')) {
            return View::make('learningcenter.contentDetailsAdmin')
                ->with('title', 'Content Details')
                ->with('page', 'learning')
                ->with('learningContent', $learningContent)
                ->with('content_details_json', $content_details_json)
                ->with('content_id', $content_id)
                ->with('section_id', $section_id)
                ->with('category_id', $category_id)
                ->with('learningQuizResult', $learningQuizResult);
        } else{
            return View::make('learningcenter.contentDetails')
                ->with('title', 'Content Details')
                ->with('page', 'learning')
                ->with('learningContent', $learningContent)
                ->with('content_details_json', $content_details_json)
                ->with('content_id', $content_id)
                ->with('section_id', $section_id)
                ->with('category_id', $category_id)
                ->with('learningQuizResult', $learningQuizResult);
        }
    }
    public function CheckQuestion(){
        $question_id = $_POST['question_id'];
        $option = $_POST['option'];
        $learningQuizQuestionAnswer = LearningQuizQuestionAnswer::where('question_id', '=', $question_id)
            ->where('is_answer', '=',1)->first();
        if($learningQuizQuestionAnswer){
            if($learningQuizQuestionAnswer->id == $option ){
                return 1;
            }else{
                return 2;
            }
        }
    }
    public function ProviderTest()
    {
        $quiz_id = Request::segment(3);
        $content_id = Request::segment(4);
        $category_id = Request::segment(5);
        $walker_id = Session::get('walker_id');
        $walker = Walker::find($walker_id);
        Session::put('quiz_id', $quiz_id);
        $learningQuizQuestion = LearningQuizQuestions::where('quiz_id', '=', $quiz_id)->get();
        // echo "<pre>";
        $questionsarray = array();
        foreach ($learningQuizQuestion as $question) {
            $learningQuizQuestionAnswer = LearningQuizQuestionAnswer::where('question_id', '=', $question->id)->get();
            $choicesArray = array();
            $choicesIDArray = array();
            foreach ($learningQuizQuestionAnswer as $option) {
                //print_r($option);
                $option_id = $option->id;
                $option_title = $option->answer;
                $tArray = array();
                $tArray['option_id'] = $option_id;
                $tArray['option_title'] = $option_title;
                array_push($choicesIDArray, $tArray);
                array_push($choicesArray, $option_title);
            }
            $questionArray = array();
            $questionArray['question_id'] = $question->id;
            $questionArray['question'] = $question->title;
            $questionArray['choices'] = $choicesArray;
            $questionArray['choicesIdArray'] = $choicesIDArray;
            $questionArray['selected_answer'] = '';
            array_push($questionsarray, $questionArray);
        }
        $answerJson = json_encode($questionsarray);
        //print_r($answerJson);die();
        return View::make('learningcenter.providerTest')
            ->with('title', 'Take a Test')
            ->with('walker', $walker)
            ->with('page', 'learning')
            ->with('learningQuestion', $learningQuizQuestion)
            ->with('answerJson', $answerJson)
            ->with('content_id',$content_id)
            ->with('category_id',$category_id);
    }
    public function AddParticipantsAnswers()
    {
        $walker_id = Session::get('walker_id');
        $questions = $_POST['result_array'];
        $quiz_id = Session::get('quiz_id');
        $completed_time = $_POST['completed_time'];
        $content_id = $_POST['content_id'];
        $category_id = $_POST['category_id'];

        if (count($questions) > 0) {
            $score = 0;
            foreach ($questions as $participant_answer) {
                //print_r($participant_answer['question_id']);
                $part_answers = new LearningQuizParticipantsAnswers;
                $part_answers->walker_id = $walker_id;
                $part_answers->quiz_id = $quiz_id;
                $part_answers->question_id = $participant_answer['question_id'];
                $part_answers->learning_quiz_question_answers_id = $participant_answer['selected_answer'];
                $part_answers->save();
                $learningQuizQuestionAnswer = LearningQuizQuestionAnswer::where('question_id', '=', $participant_answer['question_id'])
                    ->where('is_answer', '=', 1)->first();
                if ($learningQuizQuestionAnswer->id == $participant_answer['selected_answer']) {
                    $score++;
                }
            }
            $learningQuizQuestion = LearningQuizQuestions::where('quiz_id', '=', $quiz_id)->get();
            if (count($learningQuizQuestion) > 0) {
                $total_question = count($learningQuizQuestion);
                if ($score >= $total_question / 2) {
                    $result = "Pass";
                } else {
                    $result = "Fail";
                }
            }
            $data_exist = LearningQuizResults::where('content_id','=',$content_id)->first();
            if($data_exist == null) {
                Log::info('data_exist = ' . print_r($data_exist, true));
                $quiz_results = new LearningQuizResults;
                $quiz_results->walker_id = $walker_id;
                $quiz_results->quiz_id = $quiz_id;
                $quiz_results->content_id = $content_id;
                $quiz_results->score = $score;
                $quiz_results->result = $result;
                $quiz_results->completed_duration = $completed_time;
                $quiz_results->created_at = date('Y-m-d H:i:s');
                $quiz_results->updated_at = date('Y-m-d H:i:s');
                $quiz_results->save();
            }else{
                $quiz_old_result = LearningQuizResults::where('content_id','=',$content_id)->where('walker_id','=',$walker_id)->get();
                foreach ($quiz_old_result as $old_result) {
                    $old_result->deleted_at = date('Y-m-d H:i:s');
                    $old_result->save();
                }
                $quiz_results = new LearningQuizResults;
                $quiz_results->walker_id = $walker_id;
                $quiz_results->quiz_id = $quiz_id;
                $quiz_results->content_id = $content_id;
                $quiz_results->score = $score;
                $quiz_results->result = $result;
                $quiz_results->completed_duration = $completed_time;
                $quiz_results->created_at = date('Y-m-d H:i:s');
                $quiz_results->updated_at = date('Y-m-d H:i:s');
                $quiz_results->save();
            }
        }

        if($result == 'Pass') {
            Log::info('result:pass = ' . print_r($result, true));
            $learningContents = LearningContent::where('category_id', '=', $category_id)
                ->where('quiz_id', '>', 0)
                ->where('deleted_at', '=', NULL)->get();
            $learning_quiz_results = LearningQuizResults::where('walker_id','=',$walker_id)->where('deleted_at', '=', NULL)->get();
            $check_content = 0;
            $confirm_checked_content = 0;
            foreach ($learningContents as $content) {
                if ($content->category_id == $category_id) {
                    $check_content++;
                    foreach ($learning_quiz_results as $result) {
                        if ($result->content_id == $content->id && $result->result == 'Pass') {
                            $confirm_checked_content++;
                        }
                    }
                }
            }
            Log::info('result:pass check content= ' . print_r($check_content, true));
            Log::info('result:pass confirmed check content= ' . print_r($confirm_checked_content, true));

            if ($confirm_checked_content == $check_content) {
                Log::info('inner result:pass check content= ' . print_r($check_content, true));
                Log::info('inner result:pass confirmed check content= ' . print_r($confirm_checked_content, true));
                $test_parameter = array();
                $learningQuiz = LearningQuiz::where('id', '=', $quiz_id)->first();
                $walker_name = Walker::where('id', '=', $walker_id)->first();

                if ($walker_name->certificate_status != '' && $walker_name->certificate_status != null) {
                    $certificate_status_json = json_decode($walker_name->certificate_status, true);
                } else {
                    $certificate_status_json['certificate'] = array();
                }

                $newcertificateobj = array();
                $newcertificateobj['category_id'] = $category_id;
                $newcertificateobj['certificate_sent'] = "YES";
                $newcertificateobj['certificate_generate_time'] = date('Y-m-d H:i:s');

                array_push($certificate_status_json['certificate'], $newcertificateobj);
                $certificate_status_json = json_encode($certificate_status_json);
                Log::info('$certificate_status_json= ' . print_r($certificate_status_json, true));

                Walker::where('id', '=', $walker_id)->update(array('certificate_status' => $certificate_status_json));

                $learningCategory = LearningCategory::where('id','=',$category_id)->first();

                $test_parameter['category_name'] = $learningCategory->category;
                $test_parameter['driver_contact_name'] = $walker_name->contact_name;
                $test_parameter['date'] = date("Y-m-d");

                $file_name = $test_parameter['driver_contact_name'] . '_' . $test_parameter['category_name'] . '_' . uniqid() . '.pdf';
                $ext = 'pdf';
                $file_path = public_path('image') . '/uploads/' . $file_name . "." . $ext;


                try {
                    $pdf = PDF::loadView('testResultpdf', $test_parameter)->setPaper('legal')->setOrientation('portrait')->setWarnings(false);
                    $output = $pdf->output();
                } catch (Exception $exception) {
                    echo $exception;
                }
                file_put_contents($file_path, $output);

                /* Uplaod this file to s3bucket */
                if (Config::get('app.s3_bucket') != "") {

                    $s3 = App::make('aws')->get('s3');

                    $s3->putObject(array(
                        'Bucket' => Config::get('app.s3_bucket'),
                        'Key' => "uploads/learningcenter/documents/" . $file_name,
                        'SourceFile' => $file_path,
                    ));


                    $s3->putObjectAcl(array(
                        'Bucket' => Config::get('app.s3_bucket'),
                        'Key' => "uploads/learningcenter/documents/" . $file_name,
                        'ACL' => 'public-read'
                    ));

                    $final_file_name = "uploads/learningcenter/documents/" . $file_name;

                    $s3_url = $s3->getObjectUrl(Config::get('app.s3_bucket'), $final_file_name);
                    $localfile = $file_path;
                    $driver_name = array('driver_name' => $test_parameter['driver_contact_name']);
                    email_notification($walker_id, 'certificate', $driver_name, "Test Certificate", 'Certificate', null, $file_path);
                    unlink_image($localfile);
                } else {
                    $s3_url = $file_path;
                    $driver_name = array('driver_name' => $test_parameter['driver_contact_name']);
                    email_notification($walker_id, 'certificate', $driver_name, "Test Certificate", 'Certificate', null, $file_path);
                }

            }
        }
        return Redirect::to('learningcenter/participant_result');
    }
    public function ProviderResults()
    {
        $quiz_id = Session::get('quiz_id');
        $walker_id = Session::get('walker_id');
        $learningQuizQuestion = LearningQuizQuestions::where('quiz_id', '=', $quiz_id)->get();
        $learningQuizResult = LearningQuizResults::where('quiz_id', '=', $quiz_id)->where('walker_id', '=', $walker_id)->orderBy('created_at', 'desc')->first();
        if( $learningQuizResult->score > (count($learningQuizQuestion)/2) ){
            $result = 1;
        }
        else{
            $result = 0;
        }

        return View::make('learningcenter.providerResult')
            ->with('title', 'Your Test Results')
            ->with('page', 'learning')
            ->with('learningQuizQuestion', $learningQuizQuestion)
            ->with('pass_fail',$result)
            ->with('result', $learningQuizResult->score);
    }
    public function GetSectionDetails()
    {
        $section_id = $_POST['section_id'];
        $learningSections = LearningSection::where('id', '=', $section_id)->first();
        return Response::json(array('section_id' => $learningSections->id, 'title' => $learningSections->section_title,
            'description' => $learningSections->section_description));
    }
    public function AddEditSection()
    {
        $section_id = $_POST['section_id'];
        $title = $_POST['title'];
        $description = $_POST['description'];
        $category_id = Session::get('category_id');
        if ($section_id > 0) {
            LearningSection::where('id', '=', $section_id)->update(array('section_title' => $title, 'section_description' => $description));
        } else {
            $LearningSection = new LearningSection;
            $LearningSection->category_id = $category_id;
            $LearningSection->section_title = $title;
            $LearningSection->section_description = $description;
            $LearningSection->save();
        }
    }
    public function SectionDelete()
    {
        $section_id = $_POST['section_id'];
        if ($section_id > 0) {
            LearningSection::where('id', '=', $section_id)->update(array('deleted_at' => date('Y-m-d H:i:s'), 'is_active' => 0));
            LearningContent::where('section_id', '=', $section_id)->update(array('deleted_at' => date('Y-m-d H:i:s'), 'is_active' => 0));
        }
        return 1;
    }
    public function GetContents()
    {
        $content_id = $_POST['content_id'];
        $category_id = Session::get('category_id');
        $learningContent = LearningContent::where('id', '=', $content_id)
            ->where('category_id', '=', $category_id)->first();
        return Response::json(array('content_id' => $learningContent->id, 'title' => $learningContent->content,
            'description' => $learningContent->content_description, 'quiz_id' => $learningContent->quiz_id));
    }
    public function AddEditContent()
    {
        $content_id = $_POST['content_id'];
        $title = $_POST['title'];
        $description = $_POST['description'];
        $quiz_id = $_POST['quiz_id'];
        $category_id = Session::get('category_id');
        $section_id = Session::get('section_id');
        if ($content_id > 0) {
            LearningContent::where('id', '=', $content_id)->update(array('content' => $title, 'content_description' => $description, 'quiz_id' => $quiz_id));
        } else {
            $LearningContent = new LearningContent;
            $LearningContent->category_id = $category_id;
            $LearningContent->section_id = $section_id;
            $LearningContent->quiz_id = $quiz_id;
            $LearningContent->content = $title;
            $LearningContent->content_description = $description;
            $LearningContent->save();
        }
    }
    public function ContentDelete()
    {
        $content_id = $_POST['content_id'];
        if ($content_id > 0) {
            LearningContent::where('id', '=', $content_id)->update(array('deleted_at' => date('Y-m-d H:i:s'), 'is_active' => 0));
        }
        return 1;
    }
    public function AddEditTextSection()
    {
        $content_id = $_POST['content_id'];
        $title = $_POST['title'];
        $description = $_POST['description'];
        $previoustitle = $_POST['previoustitle'];
        if ($content_id > 0) {
            $learningContent = LearningContent::where('id', '=', $content_id)->first();
            if ($learningContent != '') {
                if ($learningContent->content_details_json != '' && $learningContent->content_details_json != null) {
                    //$content_details_json = json_decode(file_get_contents($learningContent->content_details_json), true);
                    $content_details_json = json_decode($learningContent->content_details_json, true);
                } else {
                    $content_details_json['sections'] = array();
                }
                if ($previoustitle != '') {
                    foreach ($content_details_json['sections'] as $index => $content) {
                        if (strcmp($content['heading'], $previoustitle) == 0) {
                            $content_details_json['sections'][$index]['heading'] = $title;
                            $content_details_json['sections'][$index]['description'] = $description;
                        }
                    }
                } else {
                    $newsectionobj = array();
                    $newsectionobj['type'] = "text";
                    $newsectionobj['heading'] = $title;
                    $newsectionobj['description'] = $description;
                    if (!(count($content_details_json['sections']) > 0)) {
                        $content_details_json['sections'] = array();
                    }
                    array_push($content_details_json['sections'], $newsectionobj);
                }
                $content_details_json = json_encode($content_details_json);
                LearningContent::where('id', '=', $content_id)->update(array('content_details_json' => $content_details_json));
                return 1;
            }
        }
    }
    public function GetTitleDetails()
    {
        $title = $_POST['title'];
        $content_id = $_POST['content_id'];
        if ($content_id > 0) {
            $learningContent = LearningContent::where('id', '=', $content_id)->first();
            if ($learningContent->content_details_json != '' && $learningContent->content_details_json != null) {
                $content_details_json = json_decode($learningContent->content_details_json, true);
                foreach ($content_details_json['sections'] as $content) {
                    if (strcmp($content['heading'], $title) == 0) {
                        return Response::json(array('content_id' => $learningContent->id, 'title' => $content['heading'],
                            'description' => $content['description']));
                    }
                }
            }
        }
    }
    public function ContentSectionDelete()
    {
        $title = $_POST['title'];
        $content_id = $_POST['content_id'];
        if ($content_id > 0) {
            $learningContent = LearningContent::where('id', '=', $content_id)->first();
            if ($learningContent->content_details_json != '' && $learningContent->content_details_json != null) {
                $content_details_json = json_decode($learningContent->content_details_json, true);
                foreach ($content_details_json['sections'] as $index => $content) {
                    if (strcmp($content['heading'], $title) == 0) {
                        unset($content_details_json['sections'][$index]);
                    }
                }
                $content_details_json = json_encode($content_details_json);
                LearningContent::where('id', '=', $content_id)->update(array('content_details_json' => $content_details_json));
                return 1;
            }
        }
    }
    public function AddEditQuizSelection()
    {
        if (Session::has('admin_id')) {
            $learningQuiz = LearningQuiz::get();
            //print_r($learningQuiz);
        } else {
            return Redirect::to('learningcenter/learning');
        }
        return View::make('learningcenter.addEditQuizSelection')
            ->with('title', 'Add/Edit Quiz')
            ->with('page', 'learning')
            ->with('learningQuiz', $learningQuiz);
    }
    public function AddEditQuiz()
    {
        if (Session::has('admin_id')) {
            $learningQuiz = LearningQuiz::get();
            error_log($learningQuiz);
        } else {
            return Redirect::to('learningcenter/learning');
        }
        return View::make('learningcenter.addEditQuiz')
            ->with('title', 'Add/Edit Quiz')
            ->with('page', 'learning')
            ->with('learningQuiz', $learningQuiz);
    }
    public function DisplayQuiz()
    {
        $quiz_id = $_POST['quiz_id'];
        $LearningQuizQuestion = LearningQuizQuestions::all();
        $LearningQuizAnswer = LearningQuizQuestionAnswer::all();
        $quiz_title = LearningQuiz::where("id", "=", $quiz_id)->pluck('quiz_name');
        Session::set('quiz_id', $quiz_id);
        Session::set('answer', $LearningQuizAnswer);
        Session::set('question', $LearningQuizQuestion);
        Session::set('quiz_title', $quiz_title);
        return View::make('learningcenter.addEditQuiz')
            ->with('title', $quiz_title)
            ->with('page', 'learning')
            ->with('answer', $LearningQuizAnswer)
            ->with('Quiz_id', $quiz_id)
            ->with('question', $LearningQuizQuestion);
    }
    public function AddNewQuiz()
    {
        $quizTitle = $_POST['quizTitle'];
        $LearningQuiz = new LearningQuiz;
        $LearningQuiz->quiz_name = $quizTitle;
        $LearningQuiz->save();
        $LearningQuizQuestion = LearningQuizQuestions::all();
        $LearningQuizAnswer = LearningQuizQuestionAnswer::all();
        Session::set('answer', $LearningQuizAnswer);
        Session::set('question', $LearningQuizQuestion);
        Session::set('quiz_id', $LearningQuiz->id);
        Session::set('quiz_title', $quizTitle);
        return ($LearningQuiz->id);
    }
    public function AddQuizQuestionAnswer()
    {
        $quiz_id = $_POST['quiz_id'];
        $question = $_POST['quizQuestion'];
        $answer_selected = $_POST['quizAnswerSelected'];
        $answer = $_POST['quizAnswer'];
        $LearningQuizQuestion = new LearningQuizQuestions();
        $LearningQuizQuestion->quiz_id = $quiz_id;
        $LearningQuizQuestion->title = $question;
        $LearningQuizQuestion->save();
        foreach ($answer as $index => $ans) {
            $LearningQuizAnswer = new LearningQuizQuestionAnswer();
            $LearningQuizAnswer->answer = $ans;
            $LearningQuizAnswer->question_id = $LearningQuizQuestion->id;
            if ($index == $answer_selected) {
                $LearningQuizAnswer->is_answer = 1;
            } else {
                $LearningQuizAnswer->is_answer = 0;
            }
            $LearningQuizAnswer->save();
        }
        $LearningQuizQuestion = LearningQuizQuestions::all();
        $LearningQuizAnswer = LearningQuizQuestionAnswer::all();
        Session::set('quiz_id', $quiz_id);
        Session::set('answer', $LearningQuizAnswer);
        Session::set('question', $LearningQuizQuestion);
    }
    public function SaveUpdatedQuiz()
    {
        $quiz_id = $_POST['quiz_id'];
        $question = $_POST['new_question'];
        $answer = $_POST['new_answer'];
        error_log(print_r($answer, TRUE));
        LearningQuizQuestions::where('id', '=', $question['key'])->update(array('title' => $question['question']));
        foreach ($answer as $ans) {
            LearningQuizQuestionAnswer::where('id', '=', $ans['key'])->update(array('answer' => $ans['answer'], 'is_answer' => $ans['is_answer_selected']));
        }
        $LearningQuizQuestion = LearningQuizQuestions::all();
        $LearningQuizAnswer = LearningQuizQuestionAnswer::all();
        Session::set('quiz_id', $quiz_id);
        Session::set('answer', $LearningQuizAnswer);
        Session::set('question', $LearningQuizQuestion);
    }
    public function DeleteQuestionAnswer()
    {
        $question_id = $_POST['question_id'];
        $quiz_id = $_POST['quiz_id'];
        if ($question_id > 0) {
            LearningQuizQuestions::where('id', '=', $question_id)->delete();
            LearningQuizQuestionAnswer::where('question_id', '=', $question_id)->delete();
        }
        $LearningQuizQuestion = LearningQuizQuestions::all();
        $LearningQuizAnswer = LearningQuizQuestionAnswer::all();
        Session::set('quiz_id', $quiz_id);
        Session::set('answer', $LearningQuizAnswer);
        Session::set('question', $LearningQuizQuestion);
    }
    public function DeleteQuiz(){
        $quiz_id = $_POST['quiz_id'];
        $LearningQuizQUestion = LearningQuizQuestions::all();
        foreach($LearningQuizQUestion as $question){
            if($question->quiz_id == $quiz_id){
                $question_id = $question->id;
                LearningQuizQuestionAnswer::where('question_id', '=', $question_id)->delete();
            }
        }
        LearningQuizQuestions::where('quiz_id', '=', $quiz_id)->delete();
        LearningQuiz::where('id', '=', $quiz_id)->delete();
    }
    public function AddEditVideoSection()
    {
        $content_id = Input::get('contentids');//$_POST['content_id'];
        $section_id = Input::get('sectionid');
        $category_id = Input::get('categoryid');
        $title = Input::get('videotitle');//$_POST['title'];
        $description = Input::get('videodescription');//$_POST['description'];
        $s3url = Input::get('url');
        $s3url = str_replace("watch?v=", "embed/", $s3url);
//        Log::info('content = ' . print_r($content_id, true));
//        Log::info('videourl1 = ' . print_r(Input::file('videourl'), true));
        if($title==''){
            return Redirect::to('/learningcenter/content_details/' . $category_id . '/' . $section_id . '/' . $content_id)->with('error', "Please enter title");
        }
        if (Input::hasFile('videourl') && Config::get('app.s3_bucket') != "") {
            Log::info('videourl2 = ' . print_r(Input::file('videourl'), true));
            $file_name = time();
            $ext = Input::file('videourl')->getClientOriginalExtension();
            $local_url = $file_name . "." . $ext;
            Log::info('videourl extension = ' . print_r($ext, true));
            Input::file('videourl')->move(public_path('image') . "/uploads", $file_name . "." . $ext);
            $s3 = App::make('aws')->get('s3');
            $s3->putObject(array(
                'Bucket' => Config::get('app.s3_bucket'),
                'Key' => "uploads/driver_learner_centre/videos/" . $local_url,
                'SourceFile' => public_path('image') . "/uploads/" . $local_url,
            ));
            $s3->putObjectAcl(array(
                'Bucket' => Config::get('app.s3_bucket'),
                'Key' => "uploads/driver_learner_centre/videos/" . $local_url,
                'ACL' => 'public-read'
            ));
            $final_file_name = "uploads/driver_learner_centre/videos/" . $local_url;
            $s3url = $s3->getObjectUrl(Config::get('app.s3_bucket'), $final_file_name);
            Log::info('s3url = ' . print_r($s3url, true));
            $localfile = public_path('image') . "/uploads/" . $local_url;
            unlink_image($localfile);
        }
        if ($content_id > 0) {
            $learningContent = LearningContent::where('id', '=', $content_id)->first();
            if ($learningContent != '') {
                if ($learningContent->content_details_json != '' && $learningContent->content_details_json != null) {
                    //$content_details_json = json_decode(file_get_contents($learningContent->content_details_json), true);
                    $content_details_json = json_decode($learningContent->content_details_json, true);
                } else {
                    $content_details_json['sections'] = array();
                }
                $newsectionobj = array();
                $newsectionobj['type'] = "video";
                $newsectionobj['heading'] = $title;
                $newsectionobj['description'] = $description;
                $newsectionobj['url'] = $s3url;
                if (!(count($content_details_json['sections']) > 0)) {
                    $content_details_json['sections'] = array();
                }
                array_push($content_details_json['sections'], $newsectionobj);
                $content_details_json = json_encode($content_details_json);
                LearningContent::where('id', '=', $content_id)->update(array('content_details_json' => $content_details_json));
                return Redirect::to('/learningcenter/content_details/' . $category_id . '/' . $section_id . '/' . $content_id);
            }
        }
    }

    public function AddEditImageSection()
    {
        $content_id = Input::get('contentid');//$_POST['content_id'];
        $section_id = Input::get('section_id');
        $category_id = Input::get('category_id');
        $title = Input::get('imagetitle');//$_POST['title'];
        $description = Input::get('imagedescription');//$_POST['description'];
        Log::info('title = ' . print_r($title, true));
        Log::info('description = ' . print_r($description, true));
        if (Input::hasFile('image') && Config::get('app.s3_bucket') != "") {
            $file_name = time();
            $ext = Input::file('image')->getClientOriginalExtension();
            $local_url = $file_name . "." . $ext;
            Input::file('image')->move(public_path('image') . "/uploads", $file_name . "." . $ext);
            $s3 = App::make('aws')->get('s3');
            $s3->putObject(array(
                'Bucket' => Config::get('app.s3_bucket'),
                'Key' => "uploads/driver_learner_centre/images/" . $local_url,
                'SourceFile' => public_path('image') . "/uploads/" . $local_url,
            ));
            $s3->putObjectAcl(array(
                'Bucket' => Config::get('app.s3_bucket'),
                'Key' => "uploads/driver_learner_centre/images/" . $local_url,
                'ACL' => 'public-read'
            ));
            $final_file_name = "uploads/driver_learner_centre/images/" . $local_url;
            $s3_url = $s3->getObjectUrl(Config::get('app.s3_bucket'), $final_file_name);
            Log::info('s3url = ' . print_r($s3_url, true));
            $localfile = public_path('image') . "/uploads/" . $local_url;
            unlink_image($localfile);
        } else {
            $s3_url = '';
        }
        if ($content_id > 0) {
            $learningContent = LearningContent::where('id', '=', $content_id)->first();
            if ($learningContent != '') {
                if ($learningContent->content_details_json != '' && $learningContent->content_details_json != null) {
                    //$content_details_json = json_decode(file_get_contents($learningContent->content_details_json), true);
                    $content_details_json = json_decode($learningContent->content_details_json, true);
                } else {
                    $content_details_json['sections'] = array();
                }
                $newsectionobj = array();
                $newsectionobj['type'] = "image";
                $newsectionobj['heading'] = $title;
                $newsectionobj['description'] = $description;
                $newsectionobj['url'] = $s3_url;
                if (!(count($content_details_json['sections']) > 0)) {
                    $content_details_json['sections'] = array();
                }
                array_push($content_details_json['sections'], $newsectionobj);
                Log::info('array = ' . print_r($content_details_json['sections'], true));
                $content_details_json = json_encode($content_details_json);
                LearningContent::where('id', '=', $content_id)->update(array('content_details_json' => $content_details_json));
                return Redirect::to('/learningcenter/content_details/' . $category_id . '/' . $section_id . '/' . $content_id);
            }
        }
    }
    public function ContentVideoDelete()
    {
        $title = $_POST['title'];
        $content_id = $_POST['content_id'];
        if ($content_id > 0) {
            $learningContent = LearningContent::where('id', '=', $content_id)->first();
            if ($learningContent->content_details_json != '' && $learningContent->content_details_json != null) {
                $content_details_json = json_decode($learningContent->content_details_json, true);
                foreach ($content_details_json['sections'] as $index => $content) {
                    if (strcmp($content['heading'], $title) == 0) {
                        unset($content_details_json['sections'][$index]);
                    }
                }
                $content_details_json = json_encode($content_details_json);
                LearningContent::where('id', '=', $content_id)->update(array('content_details_json' => $content_details_json));
                return 1;
            }
        }
    }

    public function ContentImageDelete()
    {
        $title = $_POST['title'];
        $content_id = $_POST['content_id'];
        if ($content_id > 0) {
            $learningContent = LearningContent::where('id', '=', $content_id)->first();
            if ($learningContent->content_details_json != '' && $learningContent->content_details_json != null) {
                $content_details_json = json_decode($learningContent->content_details_json, true);
                foreach ($content_details_json['sections'] as $index => $content) {
                    if (strcmp($content['heading'], $title) == 0) {
                        unset($content_details_json['sections'][$index]);
                    }
                }
                $content_details_json = json_encode($content_details_json);
                LearningContent::where('id', '=', $content_id)->update(array('content_details_json' => $content_details_json));
                return 1;
            }
        }
    }
    public function AddEditDocumentSection()
    {
        $content_id = Input::get('contentsid');//$_POST['content_id'];
        $section_id = Input::get('sectionsid');
        $category_id = Input::get('categorysid');
        $title = Input::get('doctitle');//$_POST['title'];
        $description = Input::get('docdescription');//$_POST['description'];
        Log::info('title = ' . print_r($title, true));
        Log::info('description = ' . print_r($description, true));
        if($title==''){
            return Redirect::to('/learningcenter/content_details/' . $category_id . '/' . $section_id . '/' . $content_id)->with('error', "Please enter title");
        }
        if (Input::hasFile('doc') && Config::get('app.s3_bucket') != "") {
            Log::info('input has file = ' . print_r(Input::hasFile('doc'), true));
            $file_name = time();
            $ext = Input::file('doc')->getClientOriginalExtension();
            $local_url = $file_name . "." . $ext;
            Log::info('extension = ' . print_r($ext, true));
            if($ext!= "pdf"){
                return Redirect::to('/learningcenter/content_details/' . $category_id . '/' . $section_id . '/' . $content_id)->with('error', "Please upload only pdf's");
            }
            Input::file('doc')->move(public_path('image') . "/uploads", $file_name . "." . $ext);
            $s3 = App::make('aws')->get('s3');
            $s3->putObject(array(
                'Bucket' => Config::get('app.s3_bucket'),
                'Key' => "uploads/driver_learner_centre/documents/" . $local_url,
                'SourceFile' => public_path('image') . "/uploads/" . $local_url,
            ));
            $s3->putObjectAcl(array(
                'Bucket' => Config::get('app.s3_bucket'),
                'Key' => "uploads/driver_learner_centre/documents/" . $local_url,
                'ACL' => 'public-read'
            ));
            $final_file_name = "uploads/driver_learner_centre/documents/" . $local_url;
            $s3_url = $s3->getObjectUrl(Config::get('app.s3_bucket'), $final_file_name);
            Log::info('s3url = ' . print_r($s3_url, true));
            $localfile = public_path('image') . "/uploads/" . $local_url;
            unlink_image($localfile);
        } else {
            return Redirect::to('/learningcenter/content_details/' . $category_id . '/' . $section_id . '/' . $content_id)->with('error', "Please upload a pdf");
        }
        if ($content_id > 0) {
            $learningContent = LearningContent::where('id', '=', $content_id)->first();
            if ($learningContent != '') {
                if ($learningContent->content_details_json != '' && $learningContent->content_details_json != null) {
                    //$content_details_json = json_decode(file_get_contents($learningContent->content_details_json), true);
                    $content_details_json = json_decode($learningContent->content_details_json, true);
                } else {
                    $content_details_json['sections'] = array();
                }
                $newsectionobj = array();
                $newsectionobj['type'] = "document";
                $newsectionobj['heading'] = $title;
                $newsectionobj['description'] = $description;
                $newsectionobj['url'] = $s3_url;
                if (!(count($content_details_json['sections']) > 0)) {
                    $content_details_json['sections'] = array();
                }
                array_push($content_details_json['sections'], $newsectionobj);
                Log::info('array = ' . print_r($content_details_json['sections'], true));
                $content_details_json = json_encode($content_details_json);
                LearningContent::where('id', '=', $content_id)->update(array('content_details_json' => $content_details_json));
                return Redirect::to('/learningcenter/content_details/' . $category_id . '/' . $section_id . '/' . $content_id);
            }
        }
    }
    public function ContentDocumentDelete()
    {
        $title = $_POST['title'];
        $content_id = $_POST['content_id'];
        if ($content_id > 0) {
            $learningContent = LearningContent::where('id', '=', $content_id)->first();
            if ($learningContent->content_details_json != '' && $learningContent->content_details_json != null) {
                $content_details_json = json_decode($learningContent->content_details_json, true);
                foreach ($content_details_json['sections'] as $index => $content) {
                    if (strcmp($content['heading'], $title) == 0) {
                        unset($content_details_json['sections'][$index]);
                    }
                }
                $content_details_json = json_encode($content_details_json);
                LearningContent::where('id', '=', $content_id)->update(array('content_details_json' => $content_details_json));
                return 1;
            }
        }
    }

    public function ModuleError(){

        return View::make('learningcenter.ModuleError')
            ->with('title', '')
            ->with('page', 'learning');
    }
}