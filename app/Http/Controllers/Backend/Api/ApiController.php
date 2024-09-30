<?php

namespace App\Http\Controllers\Backend\Api;

use App\Http\Controllers\Controller;
use App\Models\Goal;
use App\Models\LearningSequence;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

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


    public function handleGoogleLogin(Request $request)
    {
        try {
            // Retrieve the user information from Google using the provided token
            $googleUser = Socialite::driver('google')->stateless()->userFromToken($request->token);

            // Check if a user with the given email already exists in the database
            $user = User::where('email', $googleUser->email)->first();

            // If the user does not exist, create a new user
            if (!$user) {
                $user = User::create([
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'google_id' => $googleUser->id,
                    'profile_photo' => $googleUser->avatar,
                    'password' => Hash::make(Str::random(16)),
                    'is_profile_completed' => 0,
                    'is_verified' => 0,
                    'is_blocked' => 1,
                    'api_token' => sha1(time()),
                    'type' => 'Instructor',
                ]);

                return response()->json([
                    'message' => 'New instructor registered. Please complete your profile.',
                    'user' => $user,
                ], 201);
            } else {

                if (!$user->is_verified || !$user->is_profile_completed) {
                    $user->name = $googleUser->name;
                    $user->profile_photo = $googleUser->avatar;
                    $user->google_id = $googleUser->id;
                    $user->save();

                    return response()->json([
                        'message' => 'Complete your profile form',
                        'user' => $user,
                    ], 200);
                } else {

                    $token = $user->createToken('API Token')->plainTextToken;

                    return response()->json([
                        'message' => 'Login successful',
                        'user' => $user,
                        'token' => $token,
                    ], 200);
                }
            }
        } catch (\Exception $e) {

            return response()->json(['error' => 'Google login failed', 'message' => $e->getMessage()], 500);
        }
    }




}
