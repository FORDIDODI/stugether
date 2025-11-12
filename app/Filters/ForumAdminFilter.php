<?php

namespace App\Filters;

use App\Models\ForumModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class ForumAdminFilter implements FilterInterface
{
	public function before(RequestInterface $request, $arguments = null)
	{
		$router  = service('router');
		$params  = $router->params() ?? [];
		$forumId = (int) ($params[0] ?? 0);
		if ($forumId <= 0) {
			return $this->forbidden('Forum ID missing');
		}

		$currentUser = service('authUser')->getUser();
		if (! $currentUser) {
			return $this->forbidden('Authentication required');
		}

		$forum = (new ForumModel())->find($forumId);
		if (! $forum) {
			return $this->forbidden('Forum not found');
		}

		if ((int) $forum->admin_id !== (int) $currentUser->user_id) {
			return $this->forbidden('Admin privileges required');
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


