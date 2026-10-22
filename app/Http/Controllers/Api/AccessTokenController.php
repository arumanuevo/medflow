<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AccessToken;

class AccessTokenController extends Controller
{
    public function generate(Request $request)
    {
        return response()->json(['success' => true, 'message' => 'Token endpoint ready']);
    }

    public function list(Request $request)
    {
        return response()->json(['success' => true, 'data' => []]);
    }

    public function revoke(Request $request, $id)
    {
        return response()->json(['success' => true, 'message' => 'Token revoked']);
    }
}
