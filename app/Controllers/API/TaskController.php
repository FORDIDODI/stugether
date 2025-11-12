<?php

namespace App\Controllers\API;

use App\Models\KanbanModel;
use App\Models\ForumModel;
use App\Models\MediaModel;
use CodeIgniter\Files\File;

class TaskController extends BaseAPIController
{
	/**
	 * @OA\Post(
	 *   path="/forums/{id}/tasks",
	 *   tags={"Tasks"},
	 *   summary="Create task in forum",
	 *   security={{"bearerAuth":{}}},
	 *   @OA\Response(response=201, description="Created")
	 * )
	 */
	public function store(int $forumId)
	{
		$rules = config('Validation')->taskStore;
		if (! $this->validate($rules)) {
			return $this->fail(implode('; ', $this->validator->getErrors()), 400);
		}
		$data    = $this->request->getJSON(true) ?? $this->request->getPost();
		$current = $this->currentUser();
		$model   = new KanbanModel();
		$taskId  = $model->insert([
			'forum_id'      => $forumId,
			'judul'         => $data['judul'],
			'deskripsi'     => $data['deskripsi'] ?? null,
			'tenggat_waktu' => $data['tenggat_waktu'] ?? null,
			'file_url'      => $data['file_url'] ?? null,
			'status'        => 'todo',
			'created_by'    => $current->user_id,
		], true);
		$task = $model->find($taskId);
		return $this->success($task, 'Created', null, 201);
	}

	/**
	 * @OA\Get(
	 *   path="/forums/{id}/tasks",
	 *   tags={"Tasks"},
	 *   summary="List tasks in forum",
	 *   security={{"bearerAuth":{}}},
	 *   @OA\Response(response=200, description="OK")
	 * )
	 */
	public function index(int $forumId)
	{
		$status    = $this->request->getGet('status');
		$q         = trim((string) ($this->request->getGet('q') ?? ''));
		$sort      = $this->request->getGet('sort') ?? 'created_at';
		$page      = max(1, (int) ($this->request->getGet('page') ?? 1));
		$perPage   = min(100, max(1, (int) ($this->request->getGet('per_page') ?? 10)));

		$builder = (new KanbanModel())->builder()->where('forum_id', $forumId);
		if (in_array($status, ['todo', 'doing', 'done'], true)) {
			$builder->where('status', $status);
		}
		if ($q !== '') {
			$builder->groupStart()
				->like('judul', $q)
				->orLike('deskripsi', $q)
			->groupEnd();
		}
		$sortMap = ['deadline' => 'tenggat_waktu', 'created_at' => 'created_at'];
		$orderBy = $sortMap[$sort] ?? 'created_at';
		$builder->orderBy($orderBy, 'DESC');

		$total   = (clone $builder)->countAllResults(false);
		$data    = $builder->get(($page - 1) * $perPage, $perPage)->getResult();
		$meta    = service('paginationSvc')->buildMeta($page, $perPage, $total);
		return $this->success($data, null, $meta);
	}

	/**
	 * @OA\Get(
	 *   path="/tasks/{id}",
	 *   tags={"Tasks"},
	 *   summary="Show task",
	 *   security={{"bearerAuth":{}}},
	 *   @OA\Response(response=200, description="OK")
	 * )
	 */
	public function show(int $taskId)
	{
		$task = (new KanbanModel())->find($taskId);
		if (! $task) {
			return $this->fail('Task not found', 404);
		}
		return $this->success($task);
	}

	/**
	 * @OA\Patch(
	 *   path="/tasks/{id}",
	 *   tags={"Tasks"},
	 *   summary="Update task",
	 *   security={{"bearerAuth":{}}},
	 *   @OA\Response(response=200, description="Updated")
	 * )
	 */
	public function update(int $taskId)
	{
		$rules = config('Validation')->taskUpdate;
		if (! $this->validate($rules)) {
			return $this->fail(implode('; ', $this->validator->getErrors()), 400);
		}
		$model = new KanbanModel();
		$task  = $model->find($taskId);
		if (! $task) {
			return $this->fail('Task not found', 404);
		}
		if (! $this->canManageTask($task->forum_id, $task->created_by)) {
			return $this->fail('Forbidden', 403);
		}

		$data  = $this->request->getJSON(true) ?? $this->request->getRawInput();
		$patch = array_intersect_key($data, array_flip(['judul', 'deskripsi', 'tenggat_waktu', 'status', 'file_url']));
		$model->update($taskId, $patch);
		return $this->success($model->find($taskId), 'Updated');
	}

	/**
	 * @OA\Delete(
	 *   path="/tasks/{id}",
	 *   tags={"Tasks"},
	 *   summary="Delete task",
	 *   security={{"bearerAuth":{}}},
	 *   @OA\Response(response=200, description="Deleted")
	 * )
	 */
	public function destroy(int $taskId)
	{
		$model = new KanbanModel();
		$task  = $model->find($taskId);
		if (! $task) {
			return $this->fail('Task not found', 404);
		}
		if (! $this->canManageTask($task->forum_id, $task->created_by)) {
			return $this->fail('Forbidden', 403);
		}
		$model->delete($taskId);
		return $this->success(['ok' => true], 'Deleted');
	}

	/**
	 * @OA\Post(
	 *   path="/tasks/{id}/attachments",
	 *   tags={"Tasks"},
	 *   summary="Attach file or link to task",
	 *   security={{"bearerAuth":{}}},
	 *   @OA\Response(response=201, description="Created")
	 * )
	 */
	public function attach(int $taskId)
	{
		$task = (new KanbanModel())->find($taskId);
		if (! $task) {
			return $this->fail('Task not found', 404);
		}
		$current = $this->currentUser();
		$mediaModel = new MediaModel();

		$file = $this->request->getFile('file');
		$fileUrl = null;
		if ($file && $file->isValid()) {
			$fileUrl = $this->moveUploadedFile($file, (int) $task->forum_id);
		} else {
			$body = $this->request->getJSON(true) ?? $this->request->getPost();
			$fileUrl = $body['file_url'] ?? null;
			if (! $fileUrl) {
				return $this->fail('No file or file_url provided', 400);
			}
		}

		$mediaId = $mediaModel->insert([
			'user_id'  => $current->user_id,
			'forum_id' => $task->forum_id,
			'ref_id'   => $taskId,
			'file_url' => $fileUrl,
		], true);

		return $this->success($mediaModel->find($mediaId), 'Attached', null, 201);
	}

	private function canManageTask(int $forumId, int $createdBy): bool
	{
		$current = $this->currentUser();
		if (! $current) {
			return false;
		}
		if ((int) $createdBy === (int) $current->user_id) {
			return true;
		}
		$forum = (new ForumModel())->find($forumId);
		return $forum && (int) $forum->admin_id === (int) $current->user_id;
	}

	private function moveUploadedFile(\CodeIgniter\HTTP\Files\UploadedFile $file, int $forumId): string
	{
		$sanitized = $this->sanitizeFilename($file->getClientName());
		$subdir = 'uploads/forums/' . $forumId . '/' . gmdate('Y/m');
		$targetDir = FCPATH . $subdir;
		if (! is_dir($targetDir)) {
			mkdir($targetDir, 0775, true);
		}
		$newName = uniqid('', true) . '_' . $sanitized;
		$file->move($targetDir, $newName, true);
		return base_url($subdir . '/' . $newName);
	}

	private function sanitizeFilename(string $name): string
	{
		$name = preg_replace('/[^A-Za-z0-9._-]+/', '_', $name);
		return trim($name, '_');
	}
}


