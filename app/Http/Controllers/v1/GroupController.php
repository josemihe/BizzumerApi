<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGroupRequest;
use App\Models\Group;
use App\Models\User;
use App\Models\GroupUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request): JsonResponse
    {
        $user = Auth::guard('sanctum')->user();
        $groups = $user->inGroups()->get();
        return response()->json($groups);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): JsonResponse
    {
        $users = User::all();
        return response()->json(compact('users'), 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreGroupRequest $request): JsonResponse
    {
        $group = Group::create([
            "name" => $request->name,
            "toPay" => $request->toPay,
            "amountToPayByUser" => 0,
            "date" => $request->date,
            "comment" => $request->comment,
            "accessCode" => fake()->bothify('?????????'),
            "ownerId" => $request->user()->id,
            "status" => 0,
        ]);

        $group->participants()->attach($request->user()->id);
        $amountOfParticipants = $group->getAmountOfParticipants();
        $group->amountToPayByUser = $group->toPay / $amountOfParticipants;
        $group->save();

        return response()->json(['group' => $group], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id): JsonResponse
    {
        $group = Group::find($id);
        $participants = $group->participants;

        foreach ($participants as $member) {
            $groupUser = GroupUser::where('group_id', $group->id)
                ->where('user_id', $member->id)
                ->first();
            $member->paid = $groupUser->paid;
        }
        $this->updateGroupStatus($group);
        $group->save();

        return response()->json([
            'group' => $group,
            'participants' => $participants,
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
