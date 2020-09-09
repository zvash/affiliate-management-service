<?php

namespace App\Http\Controllers\Api\V1;

use App\Click;
use App\Services\DCMService;
use Illuminate\Http\Request;
use App\Traits\ResponseMaker;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ClickController extends Controller
{
    use ResponseMaker;

    /**
     * @param Request $request
     * @param DCMService $service
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function create(Request $request, DCMService $service)
    {
        $validator = Validator::make($request->all(), [
            'task_id' => 'required|integer|min:1',
            'user_id' => 'required|integer|min:1',
            'offer_id' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return $this->failValidation($validator->errors());
        }

        $inputs = $request->all();
        $inputs['token'] = Click::generateToken();
        $click = Click::create($inputs);
        $response = ['query_param' => env('DCM_PARAM_KEY') . '=' . $click->token];

        return $this->success($response);
    }

    /**
     * @param Request $request
     * @param $aff_click_id
     * @param $adv_sub
     * @param $sale_amount
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function registerResponse(Request $request, $aff_click_id, $adv_sub, $sale_amount)
    {
        $click = Click::where('token', $aff_click_id)->first();
        if ($click) {
            $click->order_id = $adv_sub;
            $click->amount = $sale_amount;
            $click->save();
            return $this->success(['message' => 'done']);
        }
        return $this->failMessage('Content not found', 404);
    }
}
