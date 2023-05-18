<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GroupUserController extends Controller
{
    /**
     * GroupUserController constructor.
     */

    /**
     * Update the specified resource in storage.
     * @param  $group_id
     * @param  $user_id
     * @return JsonResponse
     */
    /*public function update($group_id, $user_id): JsonResponse
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
    }*/

    /**
     * Remove the specified resource from storage.
     * @param Request $request
     * @return JsonResponse
     */
    public function destroy(Request $request): JsonResponse
    {
        $user = $request->user;
        $fields = $request->validate([
            'group_id' => 'required|string',
            'delete_id' => 'required|string'
        ]);
        $group = Group::find($fields['group_id']);
        $deleteId = $fields['delete_id'];
        if ($group->ownerId != $user->id) {
            return response()->json([
                'message' => 'Only the group owner can remove participants',
            ], 403);
        }
        if($group->ownerId == $deleteId){
            return response()->json([
                'message' => 'The admin cannot be deleted'
            ],403 );
        }
        else {
            $group->participants()->detach($deleteId);
            $group->save();

            return response()->json([
                'message' => 'Participant successfully removed',
            ], 200);
        }
    }
    public function leaveGroup(Request $request): JsonResponse
    {
        $fields = $request->validate([
            'group_id' => 'required|string',
        ]);
        $group = Group::find($fields['group_id']);
        $userId = $request->user->id;

        if ($group->ownerId != $userId) {
            $group->participants()->detach($userId);
            $group->save();

            return response()->json([
                'message' => 'Group left'
            ], 200);
        }
        else {
            $group->delete();

            return response()->json([
                'message' => 'Group deleted'
            ], 200);
        }
    }
}
