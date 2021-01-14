<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller {
    public function getAll(Request $request) {
        $params = $request->all();
        $perPage = empty($params['itemsPerPage']) ? 10 : (int) $params['itemsPerPage'];
        $users = User::query();
        $users = $this->sort($users, $params['sortBy'], $params['sortDesc'], false);
        $users = $this->finalize($users, $perPage);
        return response()->json($users);
    }

    public function create() {
        $data = [];
        $data['formData'] = $this->getFormData();
        $data['submitUrl'] = '/users';
        return response()->json($data);
    }

    public function edit(Request $request) {
        $data = [];
        $data['item'] = User::find($request->userId);
        $data['formData'] = $this->getFormData();
        $data['submitUrl'] = '/users/' . $request->userId;
        return response()->json($data);
    }

    public function show(Request $request) {
        return response()->json(User::find(request('userId')));
    }

    public function store(Request $request) {
        $data = $request->all();
        User::insert($data);
        return response()->json('success');
    }

    public function update(Request $request) {
        $data = $request->all();
        User::where('id', request('userId'))->update($data);
        return response()->json('success');
    }

    public function destroy(Request $request) {
        return response()->json(User::destroy($request->userId));
    }
    private function sort($users, $sortBy, $sortDesc, $multiSort) {
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

    private function finalize($users, $perPage) {
        return $users->paginate($perPage);
    }

    private function getFormData() {
        $data = [];
        return $data;
    }
}
