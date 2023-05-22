<?php

namespace App\Http\Controllers\v2;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Group;
use App\Models\Image;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ExpenseController extends Controller
{
    public function uploadExpense(Request $request): JsonResponse
    {
        $fields = [
            'group_id' => 'required|integer',
            'amount' => 'required|numeric',
            'description' => 'required|string',
        ];

        $validatedData = $request->validate($fields);
        $group = Group::find($validatedData['group_id']);
        $user = $request->user;

        if (!$group) {
            return response()->json(['error' => 'Group not found'], 404);
        }

        if (!$group->participants()->where('user_id', $user->id)->exists()) {
            return response()->json(['error' => 'User is not a member of this group'], 403);
        }

        $expense = new Expense();
        $expense->amount = $validatedData['amount'];
        $expense->description = $validatedData['description'];
        $expense->user_id = $user->id;
        $expense->user_name = $user->name;
        $expense->group_id = $group->id;
        $group->expenses()->save($expense);

        return response()->json(['message' => 'Expense added successfully'], 200);
    }

    public function showExpenses(Request $request):JsonResponse
    {
        $fields = [
          'group_id' => 'required|integer'
        ];
        $validatedData = $request->validate($fields);
        $expenses = Expense::where('group_id', $validatedData['group_id'])->get();
        return response()->json(['expenses' => $expenses], 200);
    }


    public function deleteExpense(Request $request): JsonResponse
    {
        $fields = [
            'expense_id' => 'required|integer',
        ];
        $validatedData = $request->validate($fields);
        $user = $request->user;

        $expense = Expense::where('id', $validatedData['expense_id'])->first();
        $groupId = $expense->group_id;
        $group = Group::where('id', $groupId)->first();

        if (!$expense) {
            return response()->json(['error' => 'Expense not found'], 404);
        }

        if ($user->id == $expense->user_id || $user->id == $group->ownerId) {
            $expense->delete();

            // Delete the associated image file
            $image = Image::where('expense_id',$validatedData['expense_id'])->first();
            if ($image) {
                $imagePath = $image->path;
                Log::info($imagePath);
                if (Storage::disk('public')->delete($imagePath)) {
                    $image->delete();
                }
                else{
                    Log::info('file not found');
                }
            }
            else{
                Log::info('image not found');
            }
            return response()->json(['message' => 'Expense deleted successfully'], 200);
        } else {
            return response()->json(['message' => 'You have no permission to delete this expense'], 200);
        }
    }

    public function calculateExpenses(Request $request): JsonResponse
    {
        $fields = [
            'group_id' => 'required|integer',
        ];
        $validatedData = $request->validate($fields);
        $group = Group::find($validatedData['group_id']);
        $participants = $group->participants;
        $expenses = $group->expenses;
        $totalExpenses = $expenses->sum('amount');
        $amountPerParticipant = $totalExpenses / $group->getAmountOfParticipants();

        $balances = [];
        $owesMoney = [];
        $owedMoney = [];
        $transactions = [];

        // Calculate the total amount paid and owed by each participant
        foreach ($participants as $participant) {
            $paid = $expenses->where('user_id', $participant->id)
                ->where('group_id', $group->id)
                ->sum('amount');
            $receives = max($paid - $amountPerParticipant, 0);
            $owes = max($amountPerParticipant - $paid,0);

            $balances[$participant->name] = [
                'paid' => $paid,
                'receives' => $receives,
                'owes' => $owes,
            ];

            if ($receives > 0) {
                $owedMoney[$participant->name] = $receives;
            } elseif ($owes > 0) {
                $owesMoney[$participant->name] = $owes;
            }
        }
        // Sort the lists by the amount owed/owed to
        arsort($owedMoney);
        arsort($owesMoney);

        foreach ($owesMoney as $pays => $amountOwed) {
            foreach ($owedMoney as $receiver => $amountOwedTo) {
                // if the debtor owes more than the creditor is owed, settle the debt
                if ($amountOwed >= $amountOwedTo) {
                    $balances[$pays]['owes'] -= $amountOwedTo;
                    $balances[$receiver]['receives'] -= $amountOwedTo;
                    $transactions[] = [
                        'from' => $pays,
                        'to' => $receiver,
                        'amount' => $amountOwedTo,
                    ];
                    unset($owesMoney[$pays]);
                    unset($owedMoney[$receiver]);
                    if ($amountOwed == $amountOwedTo) {
                        break;
                    }
                    else {
                        $amountOwed -= $amountOwedTo;
                    }
                }
                // otherwise, the debtor owes less than the creditor is owed, so partially settle the debt
                else {
                    $balances[$pays]['owes'] -= $amountOwed;
                    $balances[$receiver]['receives'] -= $amountOwed;
                    $transactions[] = [
                        'from' => $pays,
                        'to' => $receiver,
                        'amount' => $amountOwed,
                    ];
                    $owesMoney[$pays] = 0;
                    $owedMoney[$receiver] -= $amountOwed;
                    break;
                }
            }
        }
        if (empty($transactions)) {
            return response()->json([$transactions]);
        }
        return response()->json([
            'transactions' => $transactions,
        ]);
    }
}
