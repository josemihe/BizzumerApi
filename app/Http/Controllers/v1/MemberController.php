<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MemberController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $user = $request->user;
        Log::info($user);
        /*$userId = $user->id;
        Log::info($userId);*/
        $rules = [
            'accessCode' => 'required|string',
        ];

        $validatedData = $request->validate($rules);

        $group = Group::where("accessCode", "=", $validatedData['accessCode'])->first();

        // if group does not exist
        if (!($group)) {
            return response()->json(['error' => 'Failed to join the group'], 404);
        }

        if ((!$group->participants()->where('user_id', $user)->exists()) && ($group->status == 0)){
            $group->participants()->attach($user);
            $amountOfParticipants = $group->getAmountOfParticipants();
            $group->amountToPayByUser = $group->toPay / $amountOfParticipants;
            $group->save();
            return response()->json(['message' => 'Success to join the group'], 200);
        }

        return response()->json(['error' => 'Failed to join the group'], 400);
    }
}
