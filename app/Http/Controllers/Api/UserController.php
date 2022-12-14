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
        // using 'join' because in order to be able to use user_roles.label as sort
        $users = User::join('user_roles', 'user_roles.id', '=', 'users.user_role_id')
            ->select('users.*', 'user_roles.label')
            ->with('user_role');

        $users = $this->filter($users, $params);
        $users = $this->sort($users, $params['sortBy'], $params['sortDesc'], false);

        // Don't paginate the result if the request come from mobile (flutter)
        // Because in flutter used Paginated Data Table that has own pagination and sorting function
        if (array_key_exists('paginated', $params) && $params['paginated'] == 'false')
            $users = $users->get();
        else
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
        if (array_key_exists('userRole', $params) && !empty($params['userRole']) && $params['userRole'] != 'null') {
            $users->where('user_role_id', $params['userRole']);
        }
        if (array_key_exists('name', $params) && !empty($params['name']) && $params['name'] != 'null') {
            $users->where('display_name', 'like', '%' . $params['name'] . '%');
        }
        if (array_key_exists('email', $params) && !empty($params['email']) && $params['email'] != 'null') {
            $users->where('email', 'like', '%' . $params['email'] . '%');
        }
        return $users;
    }

    private function sort($users, $sortBy, $sortDesc, $multiSort)
    {
        // --- if sortBy == user_role_name
        if ($sortBy == 'user_role_name') {
            $sortBy = 'user_roles.label';
        }
        // --- END if sortBy == user_role_name

        if ($sortBy) {
            if ($multiSort) {
                foreach($sortBy  as $key => $item){
                    $users->orderBy($item, $sortDesc[$key] === 'true' ? 'desc' : 'asc');
                }
            } else {
                $users->orderBy($sortBy, $sortDesc === 'true' ? 'desc' : 'asc');
            }
        }else{
            $users->orderByDesc('id');
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
        $convert_csv = config('csv.convert');

        $filename = public_path("/csv/user_list.csv"); //save to public/csv
        $handle = fopen($filename, 'w+');
        fputcsv($handle, array('id', 'user_role_id', 'display_name', 'email', 'email_verified_at', 'password', 'create_at', 'update_at'));

        foreach($users->get() as $row) {
            $id = mb_convert_encoding($row['id'], "SJIS", "UTF-8");
            $user_role_id = mb_convert_encoding($row['user_role_id'], "SJIS", "UTF-8");
            $display_name = mb_convert_encoding($row['display_name'], "SJIS", "UTF-8");
            $email = mb_convert_encoding($row['email'], "SJIS", "UTF-8");
            if($convert_csv == "on"){
                fputcsv($handle, array(
                    $row['id'],
                    $row['user_role_id'],
                    $row['display_name'],
                    $row['email'],
                    $row['email_verified_at'],
                    $row['password'],
                    $row['created_at'],
                    $row['updated_at'],
                ));
            } else {
                fputcsv($handle, array(
                    $id,
                    $user_role_id,
                    $display_name,
                    $email,
                    $row['email_verified_at'],
                    $row['password'],
                    $row['created_at'],
                    $row['updated_at'],
                ));
            }
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
