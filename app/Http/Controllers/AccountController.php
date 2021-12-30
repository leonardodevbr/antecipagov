<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PHPUnit\Exception;

class AccountController extends Controller
{
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $account = Account::create($request->all());
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Account created successfully!',
                'data' => $account->toArray()
            ]);

        } catch (Exception $e) {

            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => []
            ], 400);
        }
    }
}
