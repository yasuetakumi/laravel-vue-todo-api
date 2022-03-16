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
        if(!is_null($item)) {
            $address = $item->prefecture . $item->city . $item->local;
            $status = 200;
        }
        else {
            $address = null;
            $status = 404;
        }
        return response()->json(['status' => $status, 'address' => $address]);
    }
}
