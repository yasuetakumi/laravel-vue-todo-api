<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Postcode;

class PostcodeController extends Controller
{
    public function address($postcode)
    {
        $item = Postcode::where('postcode', $postcode)->first();
        $address = $item->prefecture . $item->city . $item->local;
        return response()->json(['status' => 200, 'address' => $address]);
    }
}
