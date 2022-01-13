<?php

namespace App\Http\Controllers\Api;

use stdClass;
use Exception;
use App\Models\User;
use App\Models\UserRole;
use App\Imports\UsersImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserPost;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Maatwebsite\Excel\Facades\Excel;

class UserController extends Controller
{
    public function getAll(Request $request)
    {
        $params = $request->all();
        $perPage = empty($params['itemsPerPage']) ? 10 : (int) $params['itemsPerPage'];
        $users = User::select([
            'users.*',
            'user_roles.label as role_label'
        ]);
        $users->join('user_roles', 'user_roles.id', '=', 'users.user_role_id');

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

    public function store(StoreUserPost $request)
    {
        $data = $request->all();
        $data['password'] = bcrypt($data['password']);
        $data['user_role_id'] = 2;
        User::create($data);
        return successResponse();
    }

    public function update(StoreUserPost $request)
    {
        $data = $request->all();
        if (array_key_exists('password', $data) && $data['password']!==null) {
            $data['password'] = bcrypt($data['password']);
        }else{
            unset($data['password']);
        }
        User::where('id', request('userId'))->update($data);
        return successResponse();
    }

    public function destroy(Request $request)
    {
        try {
            if (Auth::user()->id == $request->userId) {
                //throw new Exception('Cannot delete self account');
                throw new Exception('Error Http 500', 500);
            }
            return successResponse(User::destroy($request->userId), "Successfully delete user");
        } catch (Exception $e) {
            // IF HTTP ERROR 500, send notification via mail and chatwork bot
            if($e->getCode() == 500){
                sendError($e->getMessage());
            }
            return errorResponse($e->getMessage());
        }
    }

    private function filter($users, $params)
    {
        if (array_key_exists('userRole', $params) && $params['userRole'] >= 1) {
            $users->where('user_role_id', $params['userRole']);
        }
        if (array_key_exists('name', $params)) {
            $users->where('display_name', 'like', '%' . $params['name'] . '%');
        }
        if (array_key_exists('email', $params)) {
            $users->where('email', 'like', '%' . $params['email'] . '%');
        }
        return $users;
    }

    private function sort($users, $sortBy, $sortDesc, $multiSort)
    {
        if ($sortDesc) {
            if ($multiSort) {
                foreach ($sortBy as $key => $item) {
                    $users->orderBy($item, $sortDesc[$key]=='true' ? 'desc' : 'asc');
                }
            } else {
                $users->orderBy($sortBy, $sortDesc=='true' ? 'desc' : 'asc');
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

    public function downloadCSV(Request $request)
    {
        $params = $request->all();
        $perPage = empty($params['itemsPerPage']) ? 10 : (int) $params['itemsPerPage'];
        $users = User::select([
            'users.*',
            'user_roles.label as role_label'
        ]);
        $users->join('user_roles', 'user_roles.id', '=', 'users.user_role_id');

        $users = $this->filter($users, $params);
        $users = $this->sort($users, $params['sortBy'], $params['sortDesc'], false);

        //create directory if not availble
        $directory = public_path() . '/csv';
        if(!File::exists($directory)){
            File::makeDirectory($directory);
        }

        $filename = public_path("/csv/user_list.csv"); //save to public/csv
        $handle = fopen($filename, 'w+');
        fputcsv($handle, array('id', 'user_roles.label', 'display_name', 'email'));

        foreach($users->get() as $row) {
            fputcsv($handle, array($row['id'], $row['role_label'], $row['display_name'], $row['email']));
        }

        fclose($handle);

        $headers = array(
            'Content-Type' => 'text/csv',
        );

        $data = response()->download($filename, 'user_list.csv', $headers);

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
