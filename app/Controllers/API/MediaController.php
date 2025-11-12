<?php

namespace App\Controllers\API;

use App\Models\MediaModel;
use App\Models\ForumModel;

class MediaController extends BaseAPIController
{
	/**
	 * @OA\Post(
	 *   path="/media",
	 *   tags={"Media"},
	 *   summary="Upload media",
	 *   security={{"bearerAuth":{}}},
	 *   @OA\Response(response=201, description="Created")
	 * )
	 */
	public function store()
	{
		// Validate via PHP side to allow optional note/ref
		$forumId = (int) ($this->request->getPost('forum_id') ?? $this->request->getJSON(true)['forum_id'] ?? 0);
		if ($forumId <= 0) {
			return $this->fail('forum_id is required', 400);
		}
		$current = $this->currentUser();
		$file    = $this->request->getFile('file');
		$fileUrl = null;
		if ($file && $file->isValid()) {
			$fileUrl = $this->moveUploadedFile($file, $forumId);
		} else {
			$body    = $this->request->getJSON(true) ?? $this->request->getPost();
			$fileUrl = $body['file_url'] ?? null;
			if (! $fileUrl) {
				return $this->fail('No file or file_url provided', 400);
			}
		}

		$noteId = (int) ($this->request->getPost('note_id') ?? $this->request->getJSON(true)['note_id'] ?? 0);
		$refId  = (int) ($this->request->getPost('ref_id') ?? $this->request->getJSON(true)['ref_id'] ?? 0);

		$id = (new MediaModel())->insert([
			'user_id'  => $current->user_id,
			'forum_id' => $forumId,
			'note_id'  => $noteId ?: null,
			'ref_id'   => $refId ?: null,
			'file_url' => $fileUrl,
		], true);

		return $this->success((new MediaModel())->find($id), 'Created', null, 201);
	}

	/**
	 * @OA\Get(
	 *   path="/forums/{id}/media",
	 *   tags={"Media"},
	 *   summary="List forum media",
	 *   security={{"bearerAuth":{}}},
	 *   @OA\Response(response=200, description="OK")
	 * )
	 */
	public function index(int $forumId)
	{
		$noteId = $this->request->getGet('note_id');
		$refId  = $this->request->getGet('ref_id');
		$builder = (new MediaModel())->builder()->where('forum_id', $forumId)->orderBy('created_at', 'DESC');
		if ($noteId) {
			$builder->where('note_id', (int) $noteId);
		}
		if ($refId) {
			$builder->where('ref_id', (int) $refId);
		}
		$data = $builder->get()->getResult();
		return $this->success($data);
	}

	/**
	 * @OA\Get(
	 *   path="/media/{id}",
	 *   tags={"Media"},
	 *   summary="Show media",
	 *   security={{"bearerAuth":{}}},
	 *   @OA\Response(response=200, description="OK")
	 * )
	 */
	public function show(int $mediaId)
	{
		$media = (new MediaModel())->find($mediaId);
		if (! $media) {
			return $this->fail('Not found', 404);
		}
		return $this->success($media);
	}

	/**
	 * @OA\Delete(
	 *   path="/media/{id}",
	 *   tags={"Media"},
	 *   summary="Delete media",
	 *   security={{"bearerAuth":{}}},
	 *   @OA\Response(response=200, description="Deleted")
	 * )
	 */
	public function destroy(int $mediaId)
	{
		$model = new MediaModel();
		$media = $model->find($mediaId);
		if (! $media) {
			return $this->fail('Not found', 404);
		}
		$current = $this->currentUser();
		$forum   = (new ForumModel())->find($media->forum_id);
		$isOwner = (int) $media->user_id === (int) $current->user_id;
		$isAdmin = $forum && (int) $forum->admin_id === (int) $current->user_id;
		if (! $isOwner && ! $isAdmin) {
			return $this->fail('Forbidden', 403);
		}
		$model->delete($mediaId);
		return $this->success(['ok' => true], 'Deleted');
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


