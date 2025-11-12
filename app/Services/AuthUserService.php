<?php

namespace App\Services;

use App\Entities\User;

/**
 * Simple request-scoped container for the authenticated user.
 */
class AuthUserService
{
	/** @var User|null */
	private ?User $user = null;

	public function setUser(?User $user): void
	{
		$this->user = $user;
	}

	public function getUser(): ?User
	{
		return $this->user;
	}
}


