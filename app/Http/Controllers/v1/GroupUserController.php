<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\Group;
use Illuminate\Http\JsonResponse;

class GroupUserController extends Controller
{
    /**
     * GroupUserController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Update the specified resource in storage.
     * @param  $group_id
     * @param  $user_id
     * @return JsonResponse
     */
    public function update($group_id, $user_id): JsonResponse
    {
        $user = auth()->user();
        $group = Group::find($group_id);

        if (!$group->participants()->where('user_id', $user->id)->exists()) {
            return response()->json([
                'message' => 'User is not a participant of this group',
            ], 403);
        }

        $group->participants()->updateExistingPivot($user_id, ['paid' => 1]);

        return response()->json([
            'message' => 'Payment status successfully updated',
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     * @param $group_id
     * @param $user_id
     * @return JsonResponse
     */
    public function destroy($group_id, $user_id): JsonResponse
    {
        $user = auth()->user();
        $group = Group::find($group_id);

        if ($group->groupOwner->id != $user->id) {
            return response()->json([
                'message' => 'Only the group owner can remove participants',
            ], 403);
        }

        if ($group->groupOwner->id == $user_id) {
            $group->delete();

            return response()->json([
                'message' => 'Group successfully deleted',
            ], 200);
        } else {
            $group->participants()->detach($user_id);
            $amountOfParticipants = $group->getAmountOfParticipants();
            $group->toPayByUser = $group->toPay / $amountOfParticipants;
            $group->save();

            return response()->json([
                'message' => 'Participant successfully removed',
            ], 200);
        }
    }
}
