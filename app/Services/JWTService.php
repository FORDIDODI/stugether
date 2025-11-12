<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Entities\User;

class JWTService
{
	/** @var string */
	private string $secret;

	/** @var string */
	private string $algo = 'HS256';

	/** @var int Default expiration seconds (7 days) */
	private int $defaultTtl = 7 * 24 * 60 * 60;

	public function __construct(string $secret)
	{
		$this->secret = $secret;
	}

	/**
	 * Issue a JWT for the given user.
	 *
	 * @param User  $user
	 * @param array $extra Additional claims to merge
	 */
	public function issueToken(User $user, array $extra = []): string
	{
		$now = time();
		$payload = array_merge([
			'sub' => $user->user_id ?? $user->id ?? null,
			'iat' => $now,
			'exp' => $now + $this->defaultTtl,
		], $extra);

		return JWT::encode($payload, $this->secret, $this->algo);
	}

	/**
	 * Verify a JWT and return the decoded payload as array or false if invalid/expired.
	 *
	 * @param string $jwt
	 * @return array|false
	 */
	public function verify(string $jwt)
	{
		try {
			$decoded = JWT::decode($jwt, new Key($this->secret, $this->algo));
			// Convert \stdClass to array recursively
			return json_decode(json_encode($decoded, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
		} catch (\Throwable $e) {
			return false;
		}
	}
}


