<?php

namespace App\Http\Controllers\v2;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Group;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class MemberController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $user = $request->user;
        $rules = [
            'access_code' => 'required|string',
        ];

        $validatedData = $request->validate($rules);

        $group = Group::where("accessCode", "=", $validatedData['access_code'])->first();

        // if group does not exist
        if (!($group)) {
            return response()->json(['error' => 'Failed to join the group'], 404);
        }

        if ((!$group->participants()->where('user_id', $user->id)->exists()) && ($group->status == 0)){
            $group->participants()->attach($user->id);
            $group->save();
            return response()->json(['message' => 'Success to join the group'], 200);
        }

        return response()->json(['error' => 'Failed to join the group'], 400);
    }

    public function sendAccessMail(Request $request): JsonResponse
    {
        try {
            $fields = $request->validate([
                'email' =>'required|email|exists:users,email',
                'group_id' => 'required|string|exists:groups,id',
            ]);

            $group = Group::findOrFail($fields['group_id']);

            // Check if the authenticated user is the owner of the group
            if ($request->user->id !== $group->ownerId) {
                return response()->json([
                    'error' => 'You do not have permission to invite users to this group.'
                ], 403);
            }

            $user = User::where('email', $fields['email'])->first();
            Mail::send('emails.group_invite', ['user' => $user, 'group'=> $group], function($message) use ($user) {
                $message->to($user->email);
                $message->subject('Group invite');
            });

            return response()->json([
                'message' => 'Invite sent successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while processing your request: ' . $e->getMessage()
            ], 500);
        }
    }
}
