<?php

namespace App\Http\Controllers\v2;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GroupUserController extends Controller
{
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
        $groupId = $fields['group_id'];
        $userId = $fields['delete_id'];

        $expenses = Expense::where('group_id', $groupId)
            ->where('user_id', $userId)
            ->get();
        Log::info('expense',$expenses->toArray());
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
            if($expenses->isNotEmpty()){
                return response()->json([
                    'message' => 'An user with expenses made cannot be deleted'
                ],403 );
            }
            else{
                $group->participants()->detach($deleteId);
                $group->save();
            }
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
