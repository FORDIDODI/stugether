<?php

namespace App\Controllers\API;

use App\Models\UserModel;
use App\Entities\User;
use OpenApi\Annotations as OA;
use OpenApi\Attributes as OAT;

class AuthController extends BaseAPIController
{
	#[OAT\Post(
		path: "/auth/register",
		tags: ["Auth"],
		summary: "Register user",
		requestBody: new OAT\RequestBody(
			required: true,
			content: new OAT\JsonContent(
				required: ["nama", "email", "password"],
				properties: [
					new OAT\Property(property: "nama", type: "string"),
					new OAT\Property(property: "email", type: "string", format: "email"),
					new OAT\Property(property: "password", type: "string", format: "password")
				]
			)
		),
		responses: [
			new OAT\Response(response: 201, description: "Registered"),
			new OAT\Response(response: 400, description: "Bad Request")
		]
	)]
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

	#[OAT\Post(
		path: "/auth/login",
		tags: ["Auth"],
		summary: "Login with email or NIM",
		requestBody: new OAT\RequestBody(
			required: true,
			content: new OAT\JsonContent(
				required: ["password"],
				properties: [
					new OAT\Property(property: "email", type: "string", format: "email", description: "Email (optional if nim provided)"),
					new OAT\Property(property: "nim", type: "string", description: "NIM (optional if email provided)"),
					new OAT\Property(property: "password", type: "string", format: "password")
				]
			)
		),
		responses: [
			new OAT\Response(response: 200, description: "Logged in"),
			new OAT\Response(response: 400, description: "Bad Request"),
			new OAT\Response(response: 401, description: "Unauthorized")
		]
	)]
	public function login()
	{
		$data = $this->request->getJSON(true) ?? $this->request->getPost();
		$email = $data['email'] ?? null;
		$nim = $data['nim'] ?? null;
		$password = $data['password'] ?? null;

		// Validate - either email or NIM must be provided
		if ((!$email && !$nim) || !$password) {
			return $this->fail('Email or NIM and password are required', 400);
		}

		$model = new UserModel();
		$user = null;

		// Try to find user by email first
		if ($email) {
			$user = $model->where('email', $email)->first();
		}
		
		// If not found by email, try NIM
		if (!$user && $nim) {
			$user = $model->where('nim', $nim)->first();
		}

		// Verify user exists and password matches
		if (!$user || !password_verify($password, (string) $user->password)) {
			return $this->fail('Invalid credentials', 401);
		}

		$token = service('jwt')->issueToken($user);
		return $this->success(['token' => $token, 'user' => $user], 'Logged in');
	}

	#[OAT\Post(
		path: "/auth/logout",
		tags: ["Auth"],
		summary: "Logout (stateless)",
		security: [["bearerAuth" => []]],
		responses: [new OAT\Response(response: 200, description: "Logged out")]
	)]
	public function logout()
	{
		return $this->success(['ok' => true], 'Logged out');
	}

	#[OAT\Get(
		path: "/auth/me",
		tags: ["Auth"],
		summary: "Current user",
		security: [["bearerAuth" => []]],
		responses: [
			new OAT\Response(response: 200, description: "User"),
			new OAT\Response(response: 401, description: "Unauthorized")
		]
	)]
	public function me()
	{
		return $this->success($this->currentUser());
	}
}
