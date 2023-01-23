<?php

namespace App\Traits;

/*
|--------------------------------------------------------------------------
| Api Responser Trait
|--------------------------------------------------------------------------
|
| This trait will be used for any response we sent to clients.
|
*/

trait ResponseTrait
{
	/**
     * Return a success JSON response.
     *
     * @param int $code
     * @param string $status
     * @param string $message
     * @param array|string $data
     * @return \Illuminate\Http\JsonResponse
     */
	protected function apiResponse(int $code, string $status, array|string $message, $items, $token = null, $access = null)
	{
        if($token != null && $access != null) {
            return response()->json([
                'status' => $status,
                'code' => $code,
                'message' => $message,
                'items' => $items,
                "access" => $access,
                'token' => $token
            ], $code);
        }
		return response()->json([
			'status' => $status,
            'code' => $code,
			'message' => $message,
			'items' => $items,
            'access' => $access
		], $code);
	}


    public function forbidden()
    {
        $code = 403;
        $status = 'warning';
        $message = '¡No tienes autorización para realizar esta acción!';
        $items = null;
        return $this->apiResponse($code, $status, $message, $items);
    }
}
