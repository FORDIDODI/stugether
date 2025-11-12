<?php

namespace App\Controllers\API;

use App\Models\UserModel;
use OpenApi\Annotations as OA;
use OpenApi\Attributes as OAT;

class UserController extends BaseAPIController
{
	#[OAT\Get(
		path: "/users/{id}",
		tags: ["Users"],
		summary: "Show user",
		security: [["bearerAuth" => []]],
		parameters: [
			new OAT\Parameter(name: "id", in: "path", required: true, schema: new OAT\Schema(type: "integer"))
		],
		responses: [
			new OAT\Response(response: 200, description: "User"),
			new OAT\Response(response: 404, description: "Not found")
		]
	)]
	public function show(int $id)
	{
		$user = (new UserModel())->find($id);
		if (! $user) {
			return $this->fail('User not found', 404);
		}
		return $this->success($user);
	}

	#[OAT\Put(
		path: "/users/{id}",
		tags: ["Users"],
		summary: "Update user (self only)",
		security: [["bearerAuth" => []]],
		parameters: [
			new OAT\Parameter(name: "id", in: "path", required: true, schema: new OAT\Schema(type: "integer"))
		],
		requestBody: new OAT\RequestBody(
			required: false,
			content: new OAT\JsonContent(
				properties: [
					new OAT\Property(property: "nim", type: "string"),
					new OAT\Property(property: "nama", type: "string"),
					new OAT\Property(property: "kelas", type: "string"),
					new OAT\Property(property: "semester", type: "integer"),
					new OAT\Property(property: "password", type: "string", format: "password"),
				]
			)
		),
		responses: [
			new OAT\Response(response: 200, description: "Updated"),
			new OAT\Response(response: 400, description: "Bad Request"),
			new OAT\Response(response: 403, description: "Forbidden")
		]
	)]
	public function update(int $id)
	{
		$current = $this->currentUser();
		if (! $current || (int) $current->user_id !== (int) $id) {
			return $this->fail('You can only update your own profile', 403);
		}
		$data  = $this->request->getJSON(true) ?? $this->request->getRawInput();
		$patch = array_intersect_key($data, array_flip(['nim', 'nama', 'kelas', 'semester']));
		if (isset($data['password']) && $data['password']) {
			$patch['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
		}
		$model = new UserModel();
		$model->update($id, $patch);
		$user = $model->find($id);
		return $this->success($user, 'Updated');
	}
}


