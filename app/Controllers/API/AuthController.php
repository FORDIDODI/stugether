<?php

namespace App\Controllers\API;

use App\Models\UserModel;
use App\Entities\User;

class AuthController extends BaseAPIController
{
	/**
	 * @OA\Post(
	 *   path="/auth/register",
	 *   tags={"Auth"},
	 *   summary="Register user",
	 *   @OA\Response(response=201, description="Registered"),
	 *   @OA\Response(response=400, description="Bad Request")
	 * )
	 */
	public function register()
	{
		$rules = config('Validation')->authRegister;
		if (! $this->validate($rules)) {
			return $this->fail(implode('; ', $this->validator->getErrors()), 400);
		}

		$data = $this->request->getJSON(true) ?? $this->request->getPost();
		$model = new UserModel();

		$userData = [
			'nama'     => $data['nama'] ?? null,
			'email'    => $data['email'],
			'password' => password_hash($data['password'], PASSWORD_BCRYPT),
		];
		$userId = $model->insert($userData, true);
		$user   = $model->find($userId);

		$token = service('jwt')->issueToken($user);

		return $this->success(['token' => $token, 'user' => $user], 'Registered',  null, 201);
	}

	/**
	 * @OA\Post(
	 *   path="/auth/login",
	 *   tags={"Auth"},
	 *   summary="Login",
	 *   @OA\Response(response=200, description="Logged in"),
	 *   @OA\Response(response=401, description="Unauthorized")
	 * )
	 */
	public function login()
	{
		$rules = config('Validation')->authLogin;
		if (! $this->validate($rules)) {
			return $this->fail(implode('; ', $this->validator->getErrors()), 400);
		}

		$data  = $this->request->getJSON(true) ?? $this->request->getPost();
		$email = $data['email'];
		$pass  = $data['password'];

		$model = new UserModel();
		$user  = $model->where('email', $email)->first();
		if (! $user || ! password_verify($pass, (string) $user->password)) {
			return $this->fail('Invalid credentials', 401);
		}

		$token = service('jwt')->issueToken($user);
		return $this->success(['token' => $token, 'user' => $user], 'Logged in');
	}

	/**
	 * @OA\Post(
	 *   path="/auth/logout",
	 *   tags={"Auth"},
	 *   summary="Logout (stateless)",
	 *   security={{"bearerAuth":{}}},
	 *   @OA\Response(response=200, description="Logged out")
	 * )
	 */
	public function logout()
	{
		return $this->success(['ok' => true], 'Logged out');
	}

	/**
	 * @OA\Get(
	 *   path="/auth/me",
	 *   tags={"Auth"},
	 *   summary="Current user",
	 *   security={{"bearerAuth":{}}},
	 *   @OA\Response(response=200, description="User")
	 * )
	 */
	public function me()
	{
		return $this->success($this->currentUser());
	}
}


