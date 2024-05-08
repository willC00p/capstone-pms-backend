<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\Transactions;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends BaseController
{
    public function index()
    {
        return Transactions::all();
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'node_customer' => 'required',
            'transaction.*.product' => 'required',
            'transaction.*.quantity' => 'required|numeric',
            'transaction.*.price' => 'required|numeric'
        ]);

        $input = $request->all();

        $transaction = DB::transaction(function () use ($input) {
            return tap(
                Transactions::create([
                    'user_id' => $input['node_customer']->id
                ]),
                function (Transactions $transaction) use ($input) {
                    $user = User::find($transaction->user_id);
                    $precedingTeamLeads = collect();

                    foreach ($user->teams as $key => $team) {
                        $teamLeadsBeforeUser = $team->leads()->get();
                        $precedingTeamLeads = $precedingTeamLeads->merge($teamLeadsBeforeUser);
                    }

                    $precedingTeamLeads = $precedingTeamLeads->unique();

                    // create a function to save shareable amount to above nodes
                    $transactions = array_map(function ($x) use ($transaction) {
                        $amount = $x['price'] * $x['quantity'];
                        $shareable = $amount * 0.1;
                        return [
                            'transaction_id' => $transaction->id,
                            'item_id' => $x['product']->id,
                            'quantity' => $x['quantity'],
                            'price' => $amount
                        ];
                    }, $input['transaction']);
                    // dd($transactions);
                    foreach ($transactions as $key => $value) {
                        $transaction->details()->create($value);
                    }

                    dd($precedingTeamLeads);
                }
            );
        });

        return response()->json([
            'success' => true,
            'transaction' => $transaction
        ]);
    }
}
