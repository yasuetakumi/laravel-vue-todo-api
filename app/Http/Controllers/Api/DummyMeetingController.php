<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DummyMeeting;
use App\Models\User;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use stdClass;

class DummyMeetingController extends Controller {
    public function getAll(Request $request) {
        $params = $request->all();
        $perPage = empty($params['itemsPerPage']) ? 10 : (int) $params['itemsPerPage'];
        $meetings = DummyMeeting::query()->with('registrant', 'customer');
        $meetings = $this->filter($meetings, $params);
        $meetings = $this->sort($meetings, $params['sortBy'], $params['sortDesc'], false);
        $meetings = $this->finalize($meetings, $perPage);

        $data = new stdClass();
        $data->meetings = $meetings;
        $data->formData = $this->getFormData();
        return successResponse($data);
    }

    public function create() {
        $data = [];
        $data['formData'] = $this->getFormData();
        $data['submitUrl'] = '/dummy-meetings';
        return successResponse($data);
    }

    public function edit(Request $request) {
        $item = DummyMeeting::find($request->meetingId);
        $item['location_image_url'] = $item['location_image_url'] ? Storage::url($item['location_image_url']) : '';

        $data = [];
        $data['item'] = $item;
        $data['formData'] = $this->getFormData();
        $data['submitUrl'] = '/dummy-meetings/' . $request->meetingId;
        return successResponse($data);
    }

    public function show(Request $request) {
        return successResponse(DummyMeeting::find(request('meetingId')));
    }

    public function store(Request $request) {
        try {
            $data = $request->all();
            if (array_key_exists('location_image', $data)) {
                $data['location_image_url'] = Storage::putFile('meetings', $data['location_image']);
                unset($data['location_image']);
            }
            unset($data['location_image_modified']);

            DummyMeeting::insert($data);
            return successResponse();
        } catch (Exception $e) {
            return errorResponse($e->getMessage());
        }
    }

    public function update(Request $request) {
        try {
            $data = $request->all();
            $meeting = DummyMeeting::find($request->meetingId);
            if ($data['location_image_modified']) {
                $fileUrl = $meeting->location_image_url;
                Storage::delete($fileUrl);
                $data['location_image_url'] = '';
            }
            if (array_key_exists('location_image', $data)) {
                $data['location_image_url'] = Storage::putFile('meetings', $data['location_image']);
                unset($data['location_image']);
            }

            $meeting->update($data);
            return successResponse();
        } catch (Exception $e) {
            return errorResponse($e->getMessage());
        }
    }

    public function destroy(Request $request) {
        return successResponse(DummyMeeting::destroy($request->meetingId));
    }

    private function filter($meetings, $params) {
        if(isset($params['title'])){
            $meetings->where('title', 'like', '%' . $params['title'] . '%');
        }
        if(isset($params['customer']) && $params['customer'] != 'null'){
            $meetings->where('customer', $params['customer']);
        }
        if( array_key_exists('location', $params) && is_numeric($params['location'])){
            $meetings->where('location', $params['location']);
        }
        if(isset($params['meeting_date_start'])){
            $meetings->whereDate('meeting_date', '>=', $params['meeting_date_start']);
        }
        if(isset($params['meeting_date_end'])){
            $meetings->whereDate('meeting_date', '<=', $params['meeting_date_end']);
        }
        if(isset($params['registrant'])  && $params['registrant'] != 'null'){
            // $meetings->where('registrant', 'like', '%' . $params['registrant'] . '%');
            $meetings->whereHas('registrant', function($query) use($params) {
                $query->where('display_name', 'like', "%{$params['registrant']}%");
            });
        }
        return $meetings;
    }

    // private function filter($meetings, $params) {
    //     if (array_key_exists('title', $params)) {
    //         $meetings->where('title', 'like', '%' . $params['title'] . '%');
    //     }
    //     if (array_key_exists('customer', $params) && $params['customer'] != 'undefined') {
    //         $meetings->where('customer', $params['customer']);
    //     }
    //     if (array_key_exists('attendee', $params) && $params['attendee'] != 'undefined') {
    //         $meetings->where('attendee', $params['attendee']);
    //     }
    //     return $meetings;
    // }

    private function sort($meetings, $sortBy, $sortDesc, $multiSort) {
        // return response()->json($sortDesc, $sortBy, $multiSort);
        if ($sortDesc) {
            if ($multiSort) {
                foreach ($sortBy as $key => $item) {
                    $meetings->orderBy($item, $sortDesc[$key] ? 'desc' : 'asc');
                }
            } else {
                if ($sortBy == 'registrant.display_name') {
                    if ($sortDesc == 'true')
                        $meetings->orderByDesc(User::select('display_name')->whereColumn('registrant', 'users.id'));
                    else if ($sortDesc == 'false')
                        $meetings->orderBy(User::select('display_name')->whereColumn('registrant', 'users.id'));
                }
                else if ($sortBy == 'customer.name') {
                    if ($sortDesc == 'true')
                        $meetings->orderByDesc(Customer::select('name')->whereColumn('customer', 'customers.id'));
                    else if ($sortDesc == 'false')
                        $meetings->orderBy(Customer::select('name')->whereColumn('customer', 'customers.id'));
                }
                else {
                    $meetings->orderBy($sortBy, $sortDesc ? 'desc' : 'asc');
                }
            }
        }
        return $meetings;
    }

    private function finalize($meetings, $perPage) {
        return $meetings->paginate($perPage);
    }

    private function getFormData() {
        $data['customers'] = Customer::all();
        $data['locations'] = DummyMeeting::LOCATION;
        $data['registrants'] = User::select('id', 'display_name', 'user_role_id')->get();
        return $data;
    }
}
