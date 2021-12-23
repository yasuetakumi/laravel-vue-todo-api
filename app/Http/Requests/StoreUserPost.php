<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreUserPost extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        //return false;
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $id = request('userId');
        if($id){
            return [
                'email' => 'required|email|unique:users,email,'.$id,
                'display_name' => 'required',
            ];
        }else{
            return [
                'email' => 'required|email|unique:users,email,null',
                'display_name' => 'required',
                'password' => 'required',
            ];
        }
        
    }
}
