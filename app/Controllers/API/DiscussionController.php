<?php

namespace App\Controllers\API;

use App\Models\DiscussionModel;
use App\Models\ForumModel;

class DiscussionController extends BaseAPIController
{
	/**
	 * @OA\Post(
	 *   path="/forums/{id}/discussions",
	 *   tags={"Discussions"},
	 *   summary="Create discussion",
	 *   security={{"bearerAuth":{}}},
	 *   @OA\Response(response=201, description="Created")
	 * )
	 */
	public function store(int $forumId)
	{
		$rules = config('Validation')->discussionStore;
		if (! $this->validate($rules)) {
			return $this->fail(implode('; ', $this->validator->getErrors()), 400);
		}
		$data    = $this->request->getJSON(true) ?? $this->request->getPost();
		$current = $this->currentUser();
		$model   = new DiscussionModel();
		$id = $model->insert([
			'forum_id'  => $forumId,
			'user_id'   => $current->user_id,
			'parent_id' => null,
			'isi'       => $data['isi'],
		], true);
		return $this->success($model->find($id), 'Created', null, 201);
	}

	/**
	 * @OA\Post(
	 *   path="/discussions/{id}/replies",
	 *   tags={"Discussions"},
	 *   summary="Reply to discussion",
	 *   security={{"bearerAuth":{}}},
	 *   @OA\Response(response=201, description="Created")
	 * )
	 */
	public function reply(int $discussionId)
	{
		$rules = config('Validation')->discussionReply;
		if (! $this->validate($rules)) {
			return $this->fail(implode('; ', $this->validator->getErrors()), 400);
		}
		$parent = (new DiscussionModel())->find($discussionId);
		if (! $parent) {
			return $this->fail('Discussion not found', 404);
		}
		$data    = $this->request->getJSON(true) ?? $this->request->getPost();
		$current = $this->currentUser();
		$model   = new DiscussionModel();
		$id = $model->insert([
			'forum_id'  => $parent->forum_id,
			'user_id'   => $current->user_id,
			'parent_id' => $discussionId,
			'isi'       => $data['isi'],
		], true);
		return $this->success($model->find($id), 'Created', null, 201);
	}

	/**
	 * @OA\Get(
	 *   path="/forums/{id}/discussions",
	 *   tags={"Discussions"},
	 *   summary="List discussions (threaded by default)",
	 *   security={{"bearerAuth":{}}},
	 *   @OA\Response(response=200, description="OK")
	 * )
	 */
	public function index(int $forumId)
	{
		$threaded = filter_var($this->request->getGet('threaded') ?? 'true', FILTER_VALIDATE_BOOLEAN);
		$q        = trim((string) ($this->request->getGet('q') ?? ''));
		$model    = new DiscussionModel();
		$builder  = $model->builder()->where('forum_id', $forumId)->orderBy('created_at', 'DESC');
		if ($q !== '') {
			$builder->like('isi', $q);
		}
		if ($threaded) {
			$rows = $builder->get()->getResultArray();
			$data = service('discussionTree')->buildTree($rows);
			return $this->success($data);
		}
		$page    = max(1, (int) ($this->request->getGet('page') ?? 1));
		$perPage = min(100, max(1, (int) ($this->request->getGet('per_page') ?? 10)));
		$total   = (clone $builder)->countAllResults(false);
		$rows    = $builder->get(($page - 1) * $perPage, $perPage)->getResult();
		$meta    = service('paginationSvc')->buildMeta($page, $perPage, $total);
		return $this->success($rows, null, $meta);
	}

	/**
	 * @OA\Get(
	 *   path="/discussions/{id}",
	 *   tags={"Discussions"},
	 *   summary="Show discussion",
	 *   security={{"bearerAuth":{}}},
	 *   @OA\Response(response=200, description="OK")
	 * )
	 */
	public function show(int $discussionId)
	{
		$disc = (new DiscussionModel())->find($discussionId);
		if (! $disc) {
			return $this->fail('Not found', 404);
		}
		return $this->success($disc);
	}

	/**
	 * @OA\Patch(
	 *   path="/discussions/{id}",
	 *   tags={"Discussions"},
	 *   summary="Update discussion",
	 *   security={{"bearerAuth":{}}},
	 *   @OA\Response(response=200, description="Updated")
	 * )
	 */
	public function update(int $discussionId)
	{
		$model = new DiscussionModel();
		$disc  = $model->find($discussionId);
		if (! $disc) {
			return $this->fail('Not found', 404);
		}
		if (! $this->canManage($disc->forum_id, $disc->user_id)) {
			return $this->fail('Forbidden', 403);
		}
		$data = $this->request->getJSON(true) ?? $this->request->getRawInput();
		$model->update($discussionId, ['isi' => $data['isi'] ?? $disc->isi]);
		return $this->success($model->find($discussionId), 'Updated');
	}

	/**
	 * @OA\Delete(
	 *   path="/discussions/{id}",
	 *   tags={"Discussions"},
	 *   summary="Delete discussion",
	 *   security={{"bearerAuth":{}}},
	 *   @OA\Response(response=200, description="Deleted")
	 * )
	 */
	public function destroy(int $discussionId)
	{
		$model = new DiscussionModel();
		$disc  = $model->find($discussionId);
		if (! $disc) {
			return $this->fail('Not found', 404);
		}
		if (! $this->canManage($disc->forum_id, $disc->user_id)) {
			return $this->fail('Forbidden', 403);
		}
		$model->delete($discussionId);
		return $this->success(['ok' => true], 'Deleted');
	}

	private function canManage(int $forumId, int $ownerId): bool
	{
		$current = $this->currentUser();
		if (! $current) {
			return false;
		}
		if ($ownerId === (int) $current->user_id) {
			return true;
		}
		$forum = (new ForumModel())->find($forumId);
		return $forum && (int) $forum->admin_id === (int) $current->user_id;
	}
}


