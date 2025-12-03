<?php

namespace App\Controllers\API;

use App\Models\NoteModel;
use App\Models\ForumModel;
use OpenApi\Annotations as OA;
use OpenApi\Attributes as OAT;

class NoteController extends BaseAPIController
{
	#[OAT\Post(
		path: "/forums/{id}/notes",
		tags: ["Notes"],
		summary: "Create note",
		security: [["bearerAuth" => []]],
		parameters: [new OAT\Parameter(name: "id", in: "path", required: true, schema: new OAT\Schema(type: "integer"))],
		requestBody: new OAT\RequestBody(
			required: true,
			content: new OAT\JsonContent(
				required: ["judul"],
				properties: [
					new OAT\Property(property: "judul", type: "string"),
					new OAT\Property(property: "kategori", type: "string"),
					new OAT\Property(property: "mata_kuliah", type: "string"),
					new OAT\Property(property: "deskripsi", type: "string")
				]
			)
		),
		responses: [
			new OAT\Response(response: 201, description: "Created"),
			new OAT\Response(response: 400, description: "Bad Request")
		]
	)]
	public function store(int $forumId)
	{
		$rules = config('Validation')->noteStore;
		if (! $this->validate($rules)) {
			return $this->fail(implode('; ', $this->validator->getErrors()), 400);
		}
		$data    = $this->request->getJSON(true) ?? $this->request->getPost();
        $current = $this->currentUser();
		$model   = new NoteModel();
		$id      = $model->insert([
			'forum_id'    => $forumId,
			'user_id'     => $current->user_id,
			'judul'       => $data['judul'],
			'kategori'    => $data['kategori'] ?? null,
			'mata_kuliah' => $data['mata_kuliah'] ?? null,
			'deskripsi'   => $data['deskripsi'] ?? null,
		], true);
		return $this->success($model->find($id), 'Created', null, 201);
	}

	#[OAT\Get(
		path: "/forums/{id}/notes",
		tags: ["Notes"],
		summary: "List notes",
		security: [["bearerAuth" => []]],
		parameters: [
			new OAT\Parameter(name: "id", in: "path", required: true, schema: new OAT\Schema(type: "integer")),
			new OAT\Parameter(name: "kategori", in: "query", required: false, schema: new OAT\Schema(type: "string")),
			new OAT\Parameter(name: "mata_kuliah", in: "query", required: false, schema: new OAT\Schema(type: "string")),
			new OAT\Parameter(name: "q", in: "query", required: false, schema: new OAT\Schema(type: "string")),
			new OAT\Parameter(name: "page", in: "query", required: false, schema: new OAT\Schema(type: "integer")),
			new OAT\Parameter(name: "per_page", in: "query", required: false, schema: new OAT\Schema(type: "integer")),
		],
		responses: [new OAT\Response(response: 200, description: "OK")]
	)]
	public function index(int $forumId)
	{
		$q           = trim((string) ($this->request->getGet('q') ?? ''));
		$kategori    = $this->request->getGet('kategori');
		$mataKuliah  = $this->request->getGet('mata_kuliah');
		$page        = max(1, (int) ($this->request->getGet('page') ?? 1));
		$perPage     = min(100, max(1, (int) ($this->request->getGet('per_page') ?? 10)));

		$builder = (new NoteModel())->builder()->where('forum_id', $forumId);
		if ($kategori) {
			$builder->where('kategori', $kategori);
		}
		if ($mataKuliah) {
			$builder->where('mata_kuliah', $mataKuliah);
		}
		if ($q !== '') {
			$builder->groupStart()
				->like('judul', $q)
				->orLike('deskripsi', $q)
			->groupEnd();
		}
		$builder->orderBy('created_at', 'DESC');
		$total   = (clone $builder)->countAllResults(false);
		$data    = $builder->get(($page - 1) * $perPage, $perPage)->getResult();
		$meta    = service('paginationSvc')->buildMeta($page, $perPage, $total);
		return $this->success($data, null, $meta);
	}

	#[OAT\Get(
		path: "/notes/{id}",
		tags: ["Notes"],
		summary: "Show note",
		security: [["bearerAuth" => []]],
		parameters: [new OAT\Parameter(name: "id", in: "path", required: true, schema: new OAT\Schema(type: "integer"))],
		responses: [
			new OAT\Response(response: 200, description: "OK"),
			new OAT\Response(response: 404, description: "Not found")
		]
	)]
	public function show(int $noteId)
	{
		$note = (new NoteModel())->find($noteId);
		if (! $note) {
			return $this->fail('Not found', 404);
		}
		return $this->success($note);
	}

	#[OAT\Patch(
		path: "/notes/{id}",
		tags: ["Notes"],
		summary: "Update note",
		security: [["bearerAuth" => []]],
		parameters: [new OAT\Parameter(name: "id", in: "path", required: true, schema: new OAT\Schema(type: "integer"))],
		requestBody: new OAT\RequestBody(
			required: false,
			content: new OAT\JsonContent(
				properties: [
					new OAT\Property(property: "judul", type: "string"),
					new OAT\Property(property: "kategori", type: "string"),
					new OAT\Property(property: "mata_kuliah", type: "string"),
					new OAT\Property(property: "deskripsi", type: "string")
				]
			)
		),
		responses: [
			new OAT\Response(response: 200, description: "Updated"),
			new OAT\Response(response: 403, description: "Forbidden")
		]
	)]
	public function update(int $noteId)
	{
		$model = new NoteModel();
		$note  = $model->find($noteId);

		if (! $note) {
			return $this->fail('Not found', 404);
		}

		if (! $this->canManage($note->forum_id, $note->user_id)) {
			return $this->fail('Forbidden', 403);
		}

		// Handle both JSON and form-urlencoded
		$data = $this->request->getJSON(true);
		if ($data === null) {
			$data = $this->request->getPost();
		}

		$patch = array_intersect_key($data, array_flip(['judul', 'kategori', 'mata_kuliah', 'deskripsi']));

		$model->update($noteId, $patch);

		return $this->success($model->find($noteId), 'Updated');
	}

	#[OAT\Delete(
		path: "/notes/{id}",
		tags: ["Notes"],
		summary: "Delete note",
		security: [["bearerAuth" => []]],
		parameters: [new OAT\Parameter(name: "id", in: "path", required: true, schema: new OAT\Schema(type: "integer"))],
		responses: [
			new OAT\Response(response: 200, description: "Deleted"),
			new OAT\Response(response: 404, description: "Not found")
		]
	)]
	public function destroy(int $noteId)
	{
		$model = new NoteModel();
		$note  = $model->find($noteId);
		if (! $note) {
			return $this->fail('Not found', 404);
		}
		if (! $this->canManage($note->forum_id, $note->user_id)) {
			return $this->fail('Forbidden', 403);
		}
		$model->delete($noteId);
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


