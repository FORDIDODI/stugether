<?php

namespace App\Controllers\API;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\ResponseInterface;
use App\Entities\User;

abstract class BaseAPIController extends Controller
{
	protected function success($data, ?string $message = null, ?array $meta = null, int $status = 200)
	{
		$payload = ['data' => $data];
		if ($meta !== null) {
			$payload['meta'] = $meta;
		}
		if ($message !== null) {
			$payload['message'] = $message;
		}
		return $this->response->setStatusCode($status)->setJSON($payload);
	}

	protected function fail(string $message, int $code = 400)
	{
		return $this->response->setStatusCode($code)->setJSON([
			'error' => [
				'code'    => $code,
				'message' => $message,
			],
		]);
	}

	/**
	 * Get the authenticated user from the request lifecycle.
	 */
	protected function currentUser(): ?User
	{
		return service('authUser')->getUser();
	}
}


