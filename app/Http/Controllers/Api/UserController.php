<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserRole;
use App\Imports\UsersImport;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use stdClass;
use Maatwebsite\Excel\Facades\Excel;

class UserController extends Controller
{
    public function getAll(Request $request)
    {
        $params = $request->all();
        $perPage = empty($params['itemsPerPage']) ? 10 : (int) $params['itemsPerPage'];
        $users = User::query();
        $users = $this->filter($users, $params);
        $users = $this->sort($users, $params['sortBy'], $params['sortDesc'], false);
        $users = $this->finalize($users, $perPage);

        $data = new stdClass();
        $data->users = $users;
        $data->formData = $this->getFormData();
        return successResponse($data);
    }

    public function create()
    {
        $data = [];
        $data['formData'] = $this->getFormData();
        $data['submitUrl'] = '/users';
        return successResponse($data);
    }

    public function edit(Request $request)
    {
        $data = [];
        $data['item'] = User::find($request->userId);
        $data['formData'] = $this->getFormData();
        $data['submitUrl'] = '/users/' . $request->userId;
        return successResponse($data);
    }

    public function show(Request $request)
    {
        return successResponse(User::find(request('userId')));
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $data['password'] = bcrypt($data['password']);
        User::insert($data);
        return successResponse();
    }

    public function update(Request $request)
    {
        $data = $request->all();
        if (array_key_exists('password', $data)) {
            $data['password'] = bcrypt($data['password']);
        }
        User::where('id', request('userId'))->update($data);
        return successResponse();
    }

    public function destroy(Request $request)
    {
        try {
            if (Auth::user()->id == $request->userId) {
                throw new Exception('Cannot delete self account');
            }
            return successResponse(User::destroy($request->userId), "Successfully delete user");
        } catch (Exception $e) {
            return errorResponse($e->getMessage());
        }
    }

    private function filter($meetings, $params)
    {
        if (array_key_exists('userRole', $params) && $params['userRole'] != 'undefined') {
            $meetings->where('user_role_id', $params['userRole']);
        }
        if (array_key_exists('name', $params)) {
            $meetings->where('display_name', 'like', '%' . $params['name'] . '%');
        }
        if (array_key_exists('email', $params)) {
            $meetings->where('email', 'like', '%' . $params['email'] . '%');
        }
        return $meetings;
    }

    private function sort($users, $sortBy, $sortDesc, $multiSort)
    {
        if ($sortDesc) {
            if ($multiSort) {
                foreach ($sortBy as $key => $item) {
                    $users->orderBy($item, $sortDesc[$key] ? 'desc' : 'asc');
                }
            } else {
                $users->orderBy($sortBy, $sortDesc ? 'desc' : 'asc');
            }
        }
        return $users;
    }

    private function finalize($users, $perPage)
    {
        return $users->paginate($perPage);
    }

    private function getFormData()
    {
        $data['userRoles'] = UserRole::all();
        return $data;
    }

    /** 
     * import user from CSV
    */
    public function importCsv(Request $request)
    {
        try{
            // --- Validate file
            $rules = [
                'file' => 'required|mimes:csv,txt'
            ];
            $messages = [
                'mimes'    => 'Since it is not in CSV format, it cannot be imported.' // EN version
            ];              
            $request->validate($rules, $messages);
            // --- END Validate file

            // --- import data
            $file = $request->file('file');
            $import = new UsersImport($request->type);
            Excel::import($import, $file);
            // --- END import data

            return successResponse($import->getArrUsers());

        } catch(\Maatwebsite\Excel\Validators\ValidationException $e){
            $failures = $e->failures();
            $messages = [];
            foreach($failures as $failure){
                array_push($messages, $failure->errors());
            }
            return errorResponse();
        }
    }
}
