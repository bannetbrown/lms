<?php

namespace App\Http\Controllers\Backend\Api;

use App\Helpers\UserHelper;
use App\Http\Controllers\Controller;
use App\Models\Goal;
use App\Models\InstructorInfo;
use App\Models\LearningSequence;
use App\Models\User;
use App\Models\UserSpecialization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use GuzzleHttp\Client;

class ApiController extends Controller
{
    public function goallist(){
        $goals = Goal::whereNull('parrent_id')->with('subGoals')->orderBy('position')->get();

        return response()->json(['message' => 'Goals fetched successfully', 'goals' => $goals]);
    }

//    public function learningsequencelist()
//    {
//        $learningSequences = LearningSequence::leftJoin('learning_sequence_goals', 'learning_sequences.id', '=', 'learning_sequence_goals.learning_sequence_id')
//            ->leftJoin('goals', 'learning_sequence_goals.goal_id', '=', 'goals.id')
//            ->leftJoin('learning_sequence_pedagogies', 'learning_sequences.id', '=', 'learning_sequence_pedagogies.learning_sequence_id')
//            ->leftJoin('learning_sequence_resources', 'learning_sequences.id', '=', 'learning_sequence_resources.learning_sequence_id')
//            ->leftJoin('pedagogy_tags', 'learning_sequence_pedagogies.pedagogy_tag_id', '=', 'pedagogy_tags.id')
//            ->leftJoin('resource_types', 'learning_sequence_resources.resource_type_id', '=', 'resource_types.id')
//            ->leftJoin('files', 'learning_sequences.id', '=', 'files.learning_sequence_id')
//            ->select(
//                'learning_sequences.id',
//                DB::raw('MAX(learning_sequences.title) as title'),
//                DB::raw('MAX(learning_sequences.description) as description'),
//                DB::raw('GROUP_CONCAT(goals.id) as parent_goal_ids'),
//                DB::raw('GROUP_CONCAT(goals.title) as assigned_goals'),
//                DB::raw('GROUP_CONCAT(files.filename) as filenames'),
//                DB::raw('GROUP_CONCAT(files.url) as urls'),
//                DB::raw('GROUP_CONCAT(DISTINCT CONCAT(pedagogy_tags.id, ":", pedagogy_tags.title) SEPARATOR ",") as pedagogy_tags'),
//                DB::raw('GROUP_CONCAT(DISTINCT CONCAT(resource_types.id, ":", resource_types.title) SEPARATOR ",") as resource_types'),
//                DB::raw('MAX(learning_sequences.created_at) as created_at'),
//                DB::raw('MAX(learning_sequences.updated_at) as updated_at'),
//
//            )
//            ->groupBy('learning_sequences.id')
//            ->orderBy('order_column', 'asc')
//            ->get();
//
//
//        $learningSequences->transform(function ($sequence) {
//            $filenames = explode(',', $sequence->filenames);
//            $sequence->filenames = implode(',', array_map('basename', $filenames));
//
//            return $sequence;
//        });
//
//        return response()->json(['message' => 'Learning Sequence fetched successfully', 'data' => $learningSequences]);
//    }


//    public function learningsequencelist()
//    {
//        $learningSequences = LearningSequence::leftJoin('learning_sequence_goals', 'learning_sequences.id', '=', 'learning_sequence_goals.learning_sequence_id')
//            ->leftJoin('goals', 'learning_sequence_goals.goal_id', '=', 'goals.id')
//            ->leftJoin('learning_sequence_pedagogies', 'learning_sequences.id', '=', 'learning_sequence_pedagogies.learning_sequence_id')
//            ->leftJoin('learning_sequence_resources', 'learning_sequences.id', '=', 'learning_sequence_resources.learning_sequence_id')
//            ->leftJoin('pedagogy_tags', 'learning_sequence_pedagogies.pedagogy_tag_id', '=', 'pedagogy_tags.id')
//            ->leftJoin('resource_types', 'learning_sequence_resources.resource_type_id', '=', 'resource_types.id')
//            ->leftJoin('files', 'learning_sequences.id', '=', 'files.learning_sequence_id')
//            ->select(
//                'learning_sequences.id',
//                DB::raw('MAX(learning_sequences.title) as title'),
//                DB::raw('MAX(learning_sequences.description) as description'),
//                DB::raw('GROUP_CONCAT(goals.id) as parent_goal_ids'),
//                DB::raw('GROUP_CONCAT(goals.title) as assigned_goals'),
//                DB::raw('GROUP_CONCAT(files.filename) as filenames'),
//                DB::raw('GROUP_CONCAT(files.url) as urls'),
//                DB::raw('GROUP_CONCAT(DISTINCT CONCAT(pedagogy_tags.id, ":", pedagogy_tags.title) SEPARATOR ",") as pedagogy_tags'),
//                DB::raw('GROUP_CONCAT(DISTINCT CONCAT(resource_types.id, ":", resource_types.title) SEPARATOR ",") as resource_types'),
//                DB::raw('MAX(learning_sequences.created_at) as created_at'),
//                DB::raw('MAX(learning_sequences.updated_at) as updated_at')
//            )
//            ->groupBy('learning_sequences.id')
//            ->orderBy('order_column', 'asc')
//            ->get();
//
//
//        $learningSequences->transform(function ($sequence) {
//            $filenames = explode(',', $sequence->filenames);
//            $sequence->filenames = implode(',', array_map('basename', $filenames));
//            $sequence->assigned_goals = explode(',', $sequence->assigned_goals);
//
//            return $sequence;
//        });
//
//        return response()->json(['message' => 'Learning Sequence fetched successfully', 'data' => $learningSequences]);
//    }


    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function handleGoogleCallback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
            $user = User::where('email', $googleUser->email)->first();

            if (!$user) {

                $user = User::create([
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'profile_photo' => $googleUser->avatar,
                    'google_id' => $googleUser->id,
                    'auth_type' => 'google',
                    'password' => Hash::make(Str::random(16)),
                    'is_profile_completed' => 0,
                    'is_blocked' => 1,
                    'api_token' => sha1(time()),
                    'type' => 'Instructor',
                ]);
            } else {
                $user->update([
                    'name' => $googleUser->name,
                    'profile_photo' => $googleUser->avatar,
                ]);
            }


            $token = $user->createToken('API Token')->plainTextToken;
            return response()->json([
                'message' => 'Instructor  authenticated successfully',
                'user' => $user,
                'token' => $token,
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Authentication failed.'], 401);
        }
    }

    public function formSave(Request $request)
    {

        if (Auth::user()->is_profile_completed) {
            return response()->json(['message' => 'Profile already completed'], 400);
        }


        $validator = Validator::make($request->all(), [
            'linkdin_link' => 'required|string',
            'gihub_id' => 'required|string',
            'google_auth_id' => 'required|email',
            'webaddress' => ['required', 'url', 'website_alive'],
            'Slack_email' => 'required|email',
            'Specialization' => 'required|array',
            'Specialization.*' => 'exists:specializations,id',
            'twiter_link' => ['required', 'url', 'twiter_link'],
            'exprience_details' => 'required|string|max:200|min:20',
        ]);

        $validator->setAttributeNames([
            'linkdin_link' => 'LinkedIn Link',
            'gihub_id' => 'GitHub ID',
            'google_auth_id' => 'Google Auth ID',
            'webaddress' => 'Web Address',
            'Slack_email' => 'Slack Email',
            'Specialization' => 'Specialization',
            'Specialization.*' => 'Selected Specialization',
            'twiter_link' => 'Twitter Link',
            'exprience_details' => 'Experience Details',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }


        $info_data = new InstructorInfo();
        $info_data->user_id = Auth::user()->id;
        $info_data->website_url = $request->webaddress;
        $info_data->google_auth_share_drive_email = $request->google_auth_id;
        $info_data->github_user_name = $request->gihub_id;
        $info_data->slack_mail_id = $request->Slack_email;
        $info_data->linkdin_link = $request->linkdin_link;
        $info_data->twiter_link = $request->twiter_link;
        $info_data->exprience_short_desc = $request->exprience_details;


        if ($info_data->save()) {

            $specializationsIds = $request->Specialization;
            $user_id = Auth::user()->id;

            $data = collect($specializationsIds)->map(function ($specializationId) use ($user_id) {
                return [
                    'user_id' => $user_id,
                    'specialization_id' => $specializationId,
                ];
            })->toArray();

            UserSpecialization::insert($data);
            User::where('id', $user_id)->update(['is_profile_completed' => 1, 'is_verified' => 0]);


            UserHelper::sent_email(Auth::user()->email, 'Registration Submitted', 'Your Account Registration process Complete. Please wait for admin approval!');

            return response()->json(['message' => 'Registration completed successfully.'], 201);
        }

        return response()->json(['error' => 'Failed to save instructor information.'], 500);
    }





}
