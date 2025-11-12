<?php

namespace App\Controllers\API;

use App\Models\ForumModel;
use App\Models\KanbanModel;
use App\Models\NoteModel;
use App\Models\DiscussionModel;
use OpenApi\Annotations as OA;
use OpenApi\Attributes as OAT;

class SearchController extends BaseAPIController
{
	#[OAT\Get(
		path: "/search",
		tags: ["Search"],
		summary: "Search across entities",
		security: [["bearerAuth" => []]],
		parameters: [
			new OAT\Parameter(name: "scope", in: "query", required: false, schema: new OAT\Schema(type: "string", enum: ["forums","tasks","notes","discussions","all"])),
			new OAT\Parameter(name: "q", in: "query", required: true, schema: new OAT\Schema(type: "string"))
		],
		responses: [new OAT\Response(response: 200, description: "OK")]
	)]
	public function index()
	{
		$scope = $this->request->getGet('scope') ?? 'all';
		$q     = trim((string) ($this->request->getGet('q') ?? ''));
		if ($q === '') {
			return $this->success([]);
		}
		$results = [];
		if ($scope === 'forums' || $scope === 'all') {
			$rows = (new ForumModel())->builder()
				->groupStart()->like('nama', $q)->orLike('deskripsi', $q)->groupEnd()
				->limit(20)->get()->getResultArray();
			foreach ($rows as $r) {
				$r['type'] = 'forum';
				$results[] = $r;
			}
		}
		if ($scope === 'tasks' || $scope === 'all') {
			$rows = (new KanbanModel())->builder()
				->groupStart()->like('judul', $q)->orLike('deskripsi', $q)->groupEnd()
				->limit(20)->get()->getResultArray();
			foreach ($rows as $r) {
				$r['type'] = 'task';
				$results[] = $r;
			}
		}
		if ($scope === 'notes' || $scope === 'all') {
			$rows = (new NoteModel())->builder()
				->groupStart()->like('judul', $q)->orLike('deskripsi', $q)->groupEnd()
				->limit(20)->get()->getResultArray();
			foreach ($rows as $r) {
				$r['type'] = 'note';
				$results[] = $r;
			}
		}
		if ($scope === 'discussions' || $scope === 'all') {
			$rows = (new DiscussionModel())->builder()
				->like('isi', $q)
				->limit(20)->get()->getResultArray();
			foreach ($rows as $r) {
				$r['type'] = 'discussion';
				$results[] = $r;
			}
		}
		return $this->success($results);
	}
}


