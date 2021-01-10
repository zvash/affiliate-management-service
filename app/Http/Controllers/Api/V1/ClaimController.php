<?php

namespace App\Http\Controllers\Api\V1;

use App\Claim;
use App\Services\AuthService;
use App\Services\WintaleShopService;
use Illuminate\Http\Request;
use App\Traits\ResponseMaker;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ClaimController extends Controller
{
    use ResponseMaker;

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|min:1',
            'claimable_type' => 'required|string|in:tasks,referrals',
            'claimable_id' => 'required|integer|min:1',
            'coin_reward' => 'required|integer|min:0',
            'remote_id' => 'string|filled',
        ]);

        if ($validator->fails()) {
            return $this->failValidation($validator->errors());
        }

        $inputs = array_filter($request->all(), function ($key) {
            return in_array($key, ['user_id', 'claimable_type', 'claimable_id', 'coin_reward', 'remote_id']);
        }, ARRAY_FILTER_USE_KEY);
        $inputs['token'] = Claim::generateToken();
        $claim = Claim::where('accepted', false)
            ->where('claimable_type', $inputs['claimable_type'])
            ->where('claimable_id', $inputs['claimable_id'])
            ->where('user_id', $inputs['user_id'])
            ->first();
        if ($claim) {
            $claim->setAttribute('token', $inputs['token'])
                ->setAttribute('coin_reward', $inputs['coin_reward'])
                ->save();
            $claim->refresh();
        } else {
            $claim = Claim::create($inputs);
        }

        $response = ['query_param' => 'claim_token=' . $claim->token];

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
            $claims = Claim::where('user_id', $userId)
                ->orderBy('updated_at', 'DESC')
                ->paginate(10, ['*'], 'page', $page);
            return $this->success($claims);
        }
        return $this->failMessage('Content not found', 404);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function acceptReferral(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|min:1',
            'claimable_id' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return $this->failValidation($validator->errors());
        }

        $claim = Claim::where('accepted', false)
            ->where('user_id', $request->get('user_id'))
            ->where('claimable_id', $request->get('claimable_id'))
            ->first();
        if ($claim) {
            $claim->accepted = true;
            $claim->save();
            return $this->success($claim);
        }
        return $this->failMessage('Content not found.', 404);
    }

    /**
     * @param Request $request
     * @param WintaleShopService $wintaleShopService
     * @param AuthService $authService
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function claim(Request $request, WintaleShopService $wintaleShopService, AuthService $authService)
    {
        $validator = Validator::make($request->all(), [
            'claim_id' => 'required|string|filled',
            'user_id' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return $this->failValidation($validator->errors());
        }

        $token = $request->get('claim_id');
        $userId = $request->get('user_id');
        $claim = Claim::where('token', $token)
            ->where('user_id', $userId)
            ->first();
        if ($claim) {
            if ($claim->claimed_at) {
                return $this->failMessage('Already claimed', 400);
            }
            if ($claim->claimable_type == 'referral') {
                if ($claim->accepted) {
                    $claim->claimed_at = date('Y-m-d H:i:s');
                    $claim->save();
                }
                return $this->success($claim);
            } else if ($claim->claimable_type == 'tasks') {
                $userData = $authService->getUserById($userId);
                if ($userData['status'] == 200) {
                    $user = $userData['data'];
                    $phone = $user['phone'];
                    $email = $user['email'];
                    $orderDate = $claim->updated_at->format('Y-m-d H:i:s');
                    $response = [];//$wintaleShopService->getOrders($click->offer_id, $orderDate, $phone, $email);
                    if ($response) {
                        $claim->accepted = true;
                        $claim->claimed_at = date('Y-m-d H:i:s');
                        $claim->save();
                        return $this->success($claim);
                    }
                    return $this->failMessage('Bad Claim', 400);
                } else {
                    return $this->failMessage($userData['data'], 400);
                }
            } else {
                return $this->failMessage('Bad Claim', 400);
            }
        } else {
            return $this->failMessage('Content not found', 404);
        }
    }
}
