<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Saldo;
use App\Models\Withdraw;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
{
    public function addBalance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'userId' => 'required|integer',
            'amount' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'code' => 400,
                'error' => $validator->errors(),
            ], 400);
        }

        //cek apakah user sudah ada
        $userAlready = Saldo::where('user_id', $request->userId)->first();

        if ($userAlready) {
            // kalo user sudah ada maka hanya menambah saldo saja
            $transaction = $userAlready->update([
                'amount' => $userAlready->amount + $request->amount,
            ]);

            if($transaction){
                return response()->json([
                    'success' => true,
                    'code' => 200,
                    'error' => null,
                ]);
            } else {
                return response()->json([
                    "success" => false,
                    "code" => 400,
                    "error" => "Gagal menambahkan dana !"
                ]);
            }
        } else {
            $transaction = Saldo::create([
                'user_id' => $request->userId,
                'amount' => $request->amount,
            ]);
    
            if($transaction){
                return response()->json([
                    'success' => true,
                    'code' => 200,
                    'error' => null,
                ]);
            } else {
                return response()->json([
                    "success" => false,
                    "code" => 400,
                    "error" => "Gagal menambahkan dana !"
                ]);
            }
        }

       
    }

    public function withdrawBalance(Request $request)
    {
        //cek input
        $validator = Validator::make($request->all(), [
            'userId' => 'required|integer',
            'amount' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'code' => 400,
                'error' => $validator->errors(),
            ], 400);
        }

        // Cek apakah amount yang diinput lebih besar dari saldo user
        $userId = $request->input('userId');
        $amount = $request->input('amount');

        $saldo = Saldo::where('user_id', $userId)->first();

        if (!$saldo || $saldo->amount < $amount) {
            return response()->json([
                'success' => false,
                'code' => 400,
                'error' => 'Gagal, dana tidak cukup untuk melakukan withdraw!'
            ], 400);
        }

        $transaction = Withdraw::create([
            'user_id' => $request->userId,
            'amount' => $request->amount,
        ]);

        if($transaction){
            //mengurangi saldo dengan nominal withdraw
            $saldo->amount -= $amount;
            $saldo->save();

            return response()->json([
                'success' => true,
                'code' => 200,
                'error' => null,
            ]);
        } else {
            return response()->json([
                "success" => false,
                "code" => 400,
                "error" => "Gagal melakukan withdraw !"
            ]);
        }
    }

    public function checkBalance($id)
    {
        $validator = Validator::make(['userId' => $id], [
            'userId' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'code' => 400,
                'error' => $validator->errors(),
            ], 400);
        }

        $saldo = Saldo::where('user_id', $id)->first();
        if ($saldo) {
            return response()->json([
                'success' => true,
                'data' => "jumlah saldo " . $saldo->amount,
                'code' => 200,
                'error' => null,
            ]);
        } else {
            return response()->json([
                'success' => false,
                'code' => 400,
                'error' => 'Gagal mengambil data balance!'
            ]);
        }
        
        
    }


}
