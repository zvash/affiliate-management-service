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
        $click = Click::where('task_id', $inputs['task_id'])
            ->where('user_id', $inputs['user_id'])
            ->whereNull('returned_amount')
            ->first();
        if ($click) {
            $click->setAttribute('token', $inputs['token'])->save();
            $click->refresh();
        } else {
            $click = Click::create($inputs);
        }
        $response = ['query_param' => env('DCM_PARAM_KEY') . '=' . $click->token];

        return $this->success($response);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function all(Request $request)
    {
        if ($request->exists('user_id')) {
            $userId = $request->get('user_id');
            $page = $request->exists('page') ? $request->get('page') : 1;
            $clicks = Click::where('user_id', $userId)
                ->orderBy('updated_at', 'DESC')
                ->paginate(2, ['*'], 'page', $page);
            return $this->success($clicks);
        }
        return $this->failMessage('Content not found', 404);
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

    public function claim(Request $request, int $taskId)
    {

    }
}
