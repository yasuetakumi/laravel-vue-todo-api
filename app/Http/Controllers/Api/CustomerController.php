<?php
// -----------------------------------------------------------------------------
namespace App\Http\Controllers\Api;
// -----------------------------------------------------------------------------

// -----------------------------------------------------------------------------
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
// -----------------------------------------------------------------------------
use App\Models\Customer;
// -----------------------------------------------------------------------------

// -----------------------------------------------------------------------------
class CustomerController extends Controller {
    // -------------------------------------------------------------------------
    public function getAll(Request $request) {
        // ---------------------------------------------------------------------
        $params    = $request->all();
        $perPage   = empty($params['itemsPerPage']) ? 10 : (int) $params['itemsPerPage'];
        $customers = Customer::query();
        $customers = $this->filter($customers, $params);
        $customers = $this->sort($customers, $params['sortBy'], $params['sortDesc'], false);
        $customers = $this->finalize($customers, $perPage);
        // ---------------------------------------------------------------------

        // ---------------------------------------------------------------------
        $data = new \stdClass();
        $data->customers = $customers;
        // ---------------------------------------------------------------------
        return successResponse($data);
        // ---------------------------------------------------------------------
    }
    // -------------------------------------------------------------------------

    // -------------------------------------------------------------------------
    public function create() {
        $data = [];
        $data['submitUrl'] = '/customers';
        return successResponse($data);
    }
    // -------------------------------------------------------------------------

    // -------------------------------------------------------------------------
    public function edit(Request $request) {
        $data = [];
        $data['item'] = Customer::find($request->customerId);
        $data['submitUrl'] = '/customers/' . $request->customerId;
        return successResponse($data);
    }
    // -------------------------------------------------------------------------

    // -------------------------------------------------------------------------
    public function show(Request $request) {
        return successResponse(Customer::find(request('customerId')));
    }
    // -------------------------------------------------------------------------

    // -------------------------------------------------------------------------
    public function store(Request $request) {
        $data = $request->all();
        Customer::create($data);
        return successResponse();
    }
    // -------------------------------------------------------------------------

    // -------------------------------------------------------------------------
    public function update(Request $request) {
        $data = $request->all();
        Customer::where('id', request('customerId'))->update($data);
        return successResponse();
    }
    // -------------------------------------------------------------------------

    // -------------------------------------------------------------------------
    public function destroy(Request $request) {
        return successResponse(Customer::destroy($request->customerId));
    }
    // -------------------------------------------------------------------------

    // -------------------------------------------------------------------------
    private function filter($meetings, $params) {
        // ---------------------------------------------------------------------
        if (array_key_exists('name', $params)) {
            $meetings->where('name', 'like', '%' . $params['name'] . '%');
        }
        if (array_key_exists('email', $params)) {
            $meetings->where('email', 'like', '%' . $params['email'] . '%');
        }
        if (array_key_exists('phone', $params)) {
            $meetings->where('phone', 'like', '%' . $params['phone'] . '%');
        }
        if (array_key_exists('website', $params)) {
            $meetings->where('website', 'like', '%' . $params['website'] . '%');
        }
        // ---------------------------------------------------------------------

        // ---------------------------------------------------------------------
        return $meetings;
        // ---------------------------------------------------------------------
    }
    // -------------------------------------------------------------------------

    // -------------------------------------------------------------------------
    private function sort($customers, $sortBy, $sortDesc, $multiSort) {
        // ---------------------------------------------------------------------
        if ($sortDesc) {
            if ($multiSort) {
                foreach ($sortBy as $key => $item) {
                    $customers->orderBy($item, $sortDesc[$key] ? 'desc' : 'asc');
                }
            }
            else {
                if ($sortDesc == 'true') $customers->orderBy($sortBy, 'desc');
                else if ($sortDesc == 'false') $customers->orderBy($sortBy);
            }
        }
        // ---------------------------------------------------------------------

        // ---------------------------------------------------------------------
        return $customers;
        // ---------------------------------------------------------------------
    }
    // -------------------------------------------------------------------------

    // -------------------------------------------------------------------------
    private function finalize($customers, $perPage) {
        return $customers->paginate($perPage);
    }
    // -------------------------------------------------------------------------
}
// -----------------------------------------------------------------------------
