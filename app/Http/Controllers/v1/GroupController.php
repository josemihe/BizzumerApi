<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGroupRequest;
use App\Models\Group;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index(Request $request): JsonResponse
    {
        $user = $request->user;
        $groups = $user->inGroups()->get();
        return response()->json([
        'groups' => $groups
    ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreGroupRequest $request): JsonResponse
    {
        $group = Group::create([
            "name" => $request->name,
            "amountToPayByUser" => 0,
            "date" => now(),
            "comment" => $request->comment,
            "accessCode" => Str::random(7),
            "ownerId" => $request->user->id,
            "status" => 0,
        ]);

        $group->participants()->attach($request->user->id);
        $group->save();
        Log::info($group);
        return response()->json(['message' => 'Group created successfully'], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request): JsonResponse
    {
        $groups = [];
        $user = $request->user;
        $fields = $request->validate([
            'id' => 'required|string',
        ]);
        $id = $fields['id'];
        $group = $user->inGroups()->where('id', $id)->first();
        $this->updateGroupStatus($group);
        $group->save();
        $groups[0]=$group;

        return response()->json([
            'groups' => $groups
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id): JsonResponse
    {
        $group = Group::find($id);
        return response()->json([
            'group' => $group
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $group = Group::find($id);
        $group->name = $request->name;
        $group->toPay = $request->toPay;
        $group->amountToPayByUser = $request->amountToPayByUser;
        $group->date = $request->date;
        $group->comment = $request->comment;
        $amountOfParticipants = $group->getAmountOfParticipants();
        $group->amountToPayByUser = $group->toPay / $amountOfParticipants;
        $this->updateGroupStatus($group);
        $group->save();
        return response()->json(['group' => $group]);
    }

    public function updateGroupStatus($group): void
    {
        $participants = $group->participants;
        $all_paid = true;
        foreach ($participants as $participant) {
            if ($participant->paid == 0) {
                $all_paid = false;
                break;
            }
        }
        if (($all_paid) && ($group->getAmountOfParticipants() > 1)) {
            $group->status = 1;
        }
    }
}
