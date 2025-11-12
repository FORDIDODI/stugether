<?php

namespace App\Controllers\API;

use App\Models\AnggotaForumModel;
use App\Models\KanbanModel;
use App\Models\DiscussionModel;
use CodeIgniter\Database\BaseConnection;
use OpenApi\Annotations as OA;
use OpenApi\Attributes as OAT;

class NotificationController extends BaseAPIController
{
	#[OAT\Get(
		path: "/notifications",
		tags: ["Notifications"],
		summary: "Get forum notifications summary",
		security: [["bearerAuth" => []]],
		responses: [new OAT\Response(response: 200, description: "OK")]
	)]
	public function index()
	{
		$db      = db_connect();
		$current = $this->currentUser();

		$forums = (new AnggotaForumModel())->builder()
			->select('forum_id')
			->where('user_id', $current->user_id)
			->get()->getResultArray();

		$forumIds = array_map(static fn($r) => (int) $r['forum_id'], $forums);
		$summary  = [];
		foreach ($forumIds as $fid) {
			$lastSeen = $this->getLastSeen($db, $current->user_id, $fid);
			$newTasks = (new KanbanModel())->where('forum_id', $fid)
				->where('created_at >', $lastSeen)->countAllResults();
			$newDiscussions = (new DiscussionModel())->where('forum_id', $fid)
				->where('created_at >', $lastSeen)->countAllResults();
			$summary[] = [
				'forum_id'        => $fid,
				'new_tasks'       => $newTasks,
				'new_discussions' => $newDiscussions,
			];
		}
		return $this->success(['forums' => $summary]);
	}

	private function getLastSeen(BaseConnection $db, int $userId, int $forumId): string
	{
		$row = $db->table('user_forum_seen')
			->where(['user_id' => $userId, 'forum_id' => $forumId])
			->get()->getRowArray();
		return $row['last_seen_at'] ?? '1970-01-01 00:00:00';
	}
}


