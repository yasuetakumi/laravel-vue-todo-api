<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DummyMeeting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DummyMeetingController extends Controller {
    public function getAll(Request $request) {
        $params = $request->all();
        $perPage = empty($params['itemsPerPage']) ? 10 : (int) $params['itemsPerPage'];
        $meetings = DummyMeeting::query();
        $meetings = $this->sort($meetings, $params['sortBy'], $params['sortDesc'], false);
        $meetings = $this->finalize($meetings, $perPage);
        return response()->json($meetings);
    }

    public function create() {
        $data = [];
        $data['formData'] = $this->getFormData();
        $data['submitUrl'] = '/dummy-meetings';
        return response()->json($data);
    }

    public function edit(Request $request) {
        $data = [];
        $data['item'] = DummyMeeting::find($request->meetingId);
        $data['formData'] = $this->getFormData();
        $data['submitUrl'] = '/dummy-meetings/' . $request->meetingId;
        return response()->json($data);
    }

    public function show(Request $request) {
        return response()->json(DummyMeeting::find(request('meetingId')));
    }

    public function store(Request $request) {
        $data = $request->all();
        if (array_key_exists('location_image', $data)) {
            Log::error($data['location_image']);
            $data['location_image_url'] = Storage::putFile('meetings', $data['location_image']);
            unset($data['location_image']);
        }

        DummyMeeting::insert($data);
        return response()->json('success');
    }

    private function sort($meetings, $sortBy, $sortDesc, $multiSort) {
        if ($sortDesc) {
            if ($multiSort) {
                foreach ($sortBy as $key => $item) {
                    $meetings->orderBy($item, $sortDesc[$key] ? 'desc' : 'asc');
                }
            } else {
                $meetings->orderBy($sortBy, $sortDesc ? 'desc' : 'asc');
            }
        }
        return $meetings;
    }

    private function finalize($meetings, $perPage) {
        return $meetings->paginate($perPage);
    }

    private function getFormData() {
        $data['customers'] = DummyMeeting::CUSTOMER;
        return $data;
    }
}
