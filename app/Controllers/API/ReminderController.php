<?php

namespace App\Controllers\API;

use App\Models\ReminderModel;
use App\Models\KanbanModel;
use App\Models\ForumModel;
use OpenApi\Annotations as OA;
use OpenApi\Attributes as OAT;

class ReminderController extends BaseAPIController
{
	#[OAT\Post(
		path: "/tasks/{id}/reminder",
		tags: ["Reminders"],
		summary: "Create reminder for task",
		security: [["bearerAuth" => []]],
		parameters: [new OAT\Parameter(name: "id", in: "path", required: true, schema: new OAT\Schema(type: "integer"))],
		requestBody: new OAT\RequestBody(
			required: true,
			content: new OAT\JsonContent(
				required: ["title","waktu"],
				properties: [
					new OAT\Property(property: "title", type: "string"),
					new OAT\Property(property: "waktu", type: "string", format: "date-time")
				]
			)
		),
		responses: [
			new OAT\Response(response: 201, description: "Created"),
			new OAT\Response(response: 400, description: "Bad Request"),
			new OAT\Response(response: 409, description: "Conflict")
		]
	)]
	public function storeForTask(int $taskId)
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

	#[OAT\Get(
		path: "/reminders",
		tags: ["Reminders"],
		summary: "List my reminders",
		security: [["bearerAuth" => []]],
		parameters: [new OAT\Parameter(name: "upcoming", in: "query", required: false, schema: new OAT\Schema(type: "boolean"))],
		responses: [new OAT\Response(response: 200, description: "OK")]
	)]
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

	#[OAT\Delete(
		path: "/reminders/{id}",
		tags: ["Reminders"],
		summary: "Delete reminder",
		security: [["bearerAuth" => []]],
		parameters: [new OAT\Parameter(name: "id", in: "path", required: true, schema: new OAT\Schema(type: "integer"))],
		responses: [
			new OAT\Response(response: 200, description: "Deleted"),
			new OAT\Response(response: 404, description: "Not found")
		]
	)]
	public function destroy(int $reminderId)
	{
		$model    = new ReminderModel();
		$reminder = $model->find($reminderId);
		
		if (! $reminder) {
			return $this->fail('Reminder not found', 404);
		}

		// Custom reminder (kanban_id null) hanya bisa dihapus oleh ownernya
		if ($reminder->kanban_id === null) {
			if ((int) $reminder->user_id !== (int) $this->currentUser()->user_id) {
				return $this->fail('Forbidden', 403);
			}
			$model->delete($reminderId);
			return $this->success(['ok' => true], 'Deleted');
		}

		// Task reminder - logic yang sudah ada
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

	#[OAT\Post(
    path: "/reminders",
    tags: ["Reminders"],
    summary: "Create custom reminder (without task)",
    security: [["bearerAuth" => []]],
    requestBody: new OAT\RequestBody(
        required: true,
        content: new OAT\JsonContent(
            required: ["title","waktu"],
            properties: [
                new OAT\Property(property: "title", type: "string"),
                new OAT\Property(property: "waktu", type: "string", format: "date-time")
            ]
        )
    ),
    responses: [
        new OAT\Response(response: 201, description: "Created"),
        new OAT\Response(response: 400, description: "Bad Request")
    ]
)]
public function store()
{
    $rules = config('Validation')->reminderStore;
    if (! $this->validate($rules)) {
        return $this->fail(implode('; ', $this->validator->getErrors()), 400);
    }

    $data = $this->request->getJSON(true) ?? $this->request->getPost();
    $current = $this->currentUser();

    $model = new ReminderModel();
    $reminderId = $model->insert([
        'kanban_id' => null, // Custom reminder tidak punya task
        'user_id'   => $current->user_id,
        'title'     => $data['title'],
        'waktu'     => $data['waktu'],
    ], true);

    return $this->success($model->find($reminderId), 'Created', null, 201);
}

}


