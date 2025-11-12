<?php

namespace App\Controllers\API;

use App\Models\UserModel;

class UserController extends BaseAPIController
{
	/**
	 * @OA\Get(
	 *   path="/users/{id}",
	 *   tags={"Users"},
	 *   summary="Show user",
	 *   security={{"bearerAuth":{}}},
	 *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
	 *   @OA\Response(response=200, description="User"),
	 *   @OA\Response(response=404, description="Not found")
	 * )
	 */
	public function show(int $id)
	{
		$user = (new UserModel())->find($id);
		if (! $user) {
			return $this->fail('User not found', 404);
		}
		return $this->success($user);
	}

	/**
	 * @OA\Put(
	 *   path="/users/{id}",
	 *   tags={"Users"},
	 *   summary="Update user (self only)",
	 *   security={{"bearerAuth":{}}},
	 *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
	 *   @OA\Response(response=200, description="Updated"),
	 *   @OA\Response(response=403, description="Forbidden")
	 * )
	 */
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


