<?php

namespace App\Controllers\API;

use App\Models\ReminderModel;
use App\Models\KanbanModel;
use App\Models\ForumModel;

class ReminderController extends BaseAPIController
{
	/**
	 * @OA\Post(
	 *   path="/tasks/{id}/reminder",
	 *   tags={"Reminders"},
	 *   summary="Create reminder for task",
	 *   security={{"bearerAuth":{}}},
	 *   @OA\Response(response=201, description="Created"),
	 *   @OA\Response(response=409, description="Conflict")
	 * )
	 */
	public function store(int $taskId)
	{
		$rules = config('Validation')->reminderStore;
		if (! $this->validate($rules)) {
			return $this->fail(implode('; ', $this->validator->getErrors()), 400);
		}
		$task = (new KanbanModel())->find($taskId);
		if (! $task) {
			return $this->fail('Task not found', 404);
		}
		$model = new ReminderModel();
		$existing = $model->where('kanban_id', $taskId)->first();
		if ($existing) {
			return $this->fail('Reminder already exists for this task', 409);
		}
		$data    = $this->request->getJSON(true) ?? $this->request->getPost();
		$current = $this->currentUser();
		$reminderId = $model->insert([
			'kanban_id' => $taskId,
			'user_id'   => $current->user_id,
			'title'     => $data['title'],
			'waktu'     => $data['waktu'],
		], true);
		return $this->success($model->find($reminderId), 'Created', null, 201);
	}

	/**
	 * @OA\Get(
	 *   path="/reminders",
	 *   tags={"Reminders"},
	 *   summary="List my reminders",
	 *   security={{"bearerAuth":{}}},
	 *   @OA\Response(response=200, description="OK")
	 * )
	 */
	public function index()
	{
		$current  = $this->currentUser();
		$upcoming = filter_var($this->request->getGet('upcoming') ?? 'true', FILTER_VALIDATE_BOOLEAN);
		$builder  = (new ReminderModel())->builder()->where('user_id', $current->user_id);
		if ($upcoming) {
			$builder->where('waktu >=', gmdate('Y-m-d H:i:s'));
		}
		$builder->orderBy('waktu', 'ASC');
		$data = $builder->get()->getResult();
		return $this->success($data);
	}

	/**
	 * @OA\Delete(
	 *   path="/reminders/{id}",
	 *   tags={"Reminders"},
	 *   summary="Delete reminder",
	 *   security={{"bearerAuth":{}}},
	 *   @OA\Response(response=200, description="Deleted")
	 * )
	 */
	public function destroy(int $reminderId)
	{
		$model    = new ReminderModel();
		$reminder = $model->find($reminderId);
		if (! $reminder) {
			return $this->fail('Reminder not found', 404);
		}
		$task = (new KanbanModel())->find($reminder->kanban_id);
		if (! $task) {
			return $this->fail('Task not found', 404);
		}
		if (! $this->canManageTask((int) $task->forum_id, (int) $task->created_by) && (int) $reminder->user_id !== (int) $this->currentUser()->user_id) {
			return $this->fail('Forbidden', 403);
		}
		$model->delete($reminderId);
		return $this->success(['ok' => true], 'Deleted');
	}

	private function canManageTask(int $forumId, int $createdBy): bool
	{
		$current = $this->currentUser();
		if (! $current) {
			return false;
		}
		if ($createdBy === (int) $current->user_id) {
			return true;
		}
		$forum = (new ForumModel())->find($forumId);
		return $forum && (int) $forum->admin_id === (int) $current->user_id;
	}
}


