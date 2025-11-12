<?php

namespace App\Filters;

use App\Models\ForumModel;
use App\Models\AnggotaForumModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class ForumMemberFilter implements FilterInterface
{
	public function before(RequestInterface $request, $arguments = null)
	{
		$router  = service('router');
		$params  = $router->params() ?? [];
		$forumId = (int) ($params[0] ?? 0);
		if ($forumId <= 0) {
			return $this->forbidden('Forum ID missing');
		}

		$method = strtoupper($request->getMethod());
		$forum  = (new ForumModel())->find($forumId);
		if (! $forum) {
			return $this->forbidden('Forum not found');
		}

		// Allow public forum reads
		if ($method === 'GET' && (int) ($forum->is_public ?? 0) === 1) {
			return null;
		}

		$currentUser = service('authUser')->getUser();
		if (! $currentUser) {
			return $this->forbidden('Authentication required');
		}

		$member = (new AnggotaForumModel())->where([
			'forum_id' => $forumId,
			'user_id'  => $currentUser->user_id,
		])->first();

		if (! $member) {
			return $this->forbidden('Membership required');
		}

		return null;
	}

	public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
	{
		// no-op
	}

	private function forbidden(string $message)
	{
		$response = service('response');
		$response->setStatusCode(403);
		$response->setJSON([
			'error' => [
				'code'    => 403,
				'message' => $message,
			],
		]);
		return $response;
	}
}


