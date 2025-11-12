<?php

namespace App\Controllers\API;

use App\Models\ForumModel;
use App\Models\AnggotaForumModel;
use App\Models\UserModel;
use OpenApi\Annotations as OA;
use OpenApi\Attributes as OAT;

class ForumMemberController extends BaseAPIController
{
	#[OAT\Post(
		path: "/forums/{id}/join",
		tags: ["Forums"],
		summary: "Join forum by kode_undangan",
		security: [["bearerAuth" => []]],
		parameters: [
			new OAT\Parameter(name: "id", in: "path", required: true, schema: new OAT\Schema(type: "integer"))
		],
		requestBody: new OAT\RequestBody(
			required: true,
			content: new OAT\JsonContent(
				required: ["kode_undangan"],
				properties: [new OAT\Property(property: "kode_undangan", type: "string")]
			)
		),
		responses: [
			new OAT\Response(response: 200, description: "Joined"),
			new OAT\Response(response: 400, description: "Bad Request")
		]
	)]
	public function join(int $forumId)
	{
		$rules = config('Validation')->forumJoin;
		if (! $this->validate($rules)) {
			return $this->fail(implode('; ', $this->validator->getErrors()), 400);
		}
		$data  = $this->request->getJSON(true) ?? $this->request->getPost();
		$kode  = $data['kode_undangan'];
		$forum = (new ForumModel())->find($forumId);
		if (! $forum) {
			return $this->fail('Forum not found', 404);
		}
		if ($forum->kode_undangan !== $kode) {
			return $this->fail('Invalid invitation code', 400);
		}
		$user   = $this->currentUser();
		$model  = new AnggotaForumModel();
		$exists = $model->where(['forum_id' => $forumId, 'user_id' => $user->user_id])->first();
		if (! $exists) {
			$model->insert([
				'forum_id'       => $forumId,
				'user_id'        => $user->user_id,
				'allowed_upload' => 0,
			]);
		}
		return $this->success(['ok' => true], 'Joined');
	}

	#[OAT\Post(
		path: "/forums/{id}/leave",
		tags: ["Forums"],
		summary: "Leave forum",
		security: [["bearerAuth" => []]],
		parameters: [new OAT\Parameter(name: "id", in: "path", required: true, schema: new OAT\Schema(type: "integer"))],
		responses: [
			new OAT\Response(response: 200, description: "Left"),
			new OAT\Response(response: 403, description: "Forbidden")
		]
	)]
	public function leave(int $forumId)
	{
		$current = $this->currentUser();
		$forum   = (new ForumModel())->find($forumId);
		if (! $forum) {
			return $this->fail('Forum not found', 404);
		}
		if ((int) $forum->admin_id === (int) $current->user_id) {
			return $this->fail('Admin cannot leave the forum', 403);
		}
		$model = new AnggotaForumModel();
		$model->where(['forum_id' => $forumId, 'user_id' => $current->user_id])->delete();
		return $this->success(['ok' => true], 'Left forum');
	}

	#[OAT\Get(
		path: "/forums/{id}/members",
		tags: ["Forums"],
		summary: "List forum members",
		security: [["bearerAuth" => []]],
		parameters: [new OAT\Parameter(name: "id", in: "path", required: true, schema: new OAT\Schema(type: "integer"))],
		responses: [new OAT\Response(response: 200, description: "OK")]
	)]
	public function members(int $forumId)
	{
		$builder = (new AnggotaForumModel())->builder()
			->select('u.user_id, u.nama, u.email, af.allowed_upload, af.joined_at')
			->from('anggota_forum af')
			->join('users u', 'u.user_id = af.user_id', 'inner')
			->where('af.forum_id', $forumId)
			->orderBy('u.nama', 'ASC');
		$rows = $builder->get()->getResultArray();
		return $this->success($rows);
	}

	#[OAT\Patch(
		path: "/forums/{id}/members/{userId}",
		tags: ["Forums"],
		summary: "Update member allowed_upload (admin)",
		security: [["bearerAuth" => []]],
		parameters: [
			new OAT\Parameter(name: "id", in: "path", required: true, schema: new OAT\Schema(type: "integer")),
			new OAT\Parameter(name: "userId", in: "path", required: true, schema: new OAT\Schema(type: "integer")),
		],
		requestBody: new OAT\RequestBody(
			required: true,
			content: new OAT\JsonContent(
				required: ["allowed_upload"],
				properties: [new OAT\Property(property: "allowed_upload", type: "integer", enum: [0,1])]
			)
		),
		responses: [new OAT\Response(response: 200, description: "Updated")]
	)]
	public function update(int $forumId, int $userId)
	{
		$rules = config('Validation')->memberUpdate;
		if (! $this->validate($rules)) {
			return $this->fail(implode('; ', $this->validator->getErrors()), 400);
		}
		$data = $this->request->getJSON(true) ?? $this->request->getRawInput();
		(new AnggotaForumModel())->where(['forum_id' => $forumId, 'user_id' => $userId])
			->set(['allowed_upload' => (int) $data['allowed_upload']])
			->update();

		return $this->success(['ok' => true], 'Updated');
	}
}


