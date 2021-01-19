<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DummyMeeting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use stdClass;

class DummyMeetingController extends Controller {
    public function getAll(Request $request) {
        $data = new stdClass();
        $params = $request->all();
        $perPage = empty($params['itemsPerPage']) ? 10 : (int) $params['itemsPerPage'];
        $meetings = DummyMeeting::query();
        $meetings = $this->filter($meetings, $params);
        $meetings = $this->sort($meetings, $params['sortBy'], $params['sortDesc'], false);
        $meetings = $this->finalize($meetings, $perPage);
        $data->meetings = $meetings;
        $data->formData = $this->getFormData();
        return response()->json($data);
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

    public function destroy(Request $request) {
        return response()->json(DummyMeeting::destroy($request->meetingId));
    }

    private function filter($meetings, $params) {
        if (array_key_exists('title', $params)) {
            $meetings->where('title', 'like', '%' . $params['title'] . '%');
        }
        if (array_key_exists('customer', $params)) {
            $meetings->where('customer', $params['customer']);
        }
        if (array_key_exists('attendee', $params)) {
            $meetings->where('attendee', $params['attendee']);
        }
        return $meetings;
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
        $data['attendees'] = DummyMeeting::ATTENDEE;
        return $data;
    }
}
