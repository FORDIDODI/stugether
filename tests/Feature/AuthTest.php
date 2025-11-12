<?php

namespace Tests\Feature;

use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\CIUnitTestCase;

final class AuthTest extends CIUnitTestCase
{
	use FeatureTestTrait;

	public function testRegisterLoginMe(): void
	{
		// Register
		$res = $this->post('auth/register', [
			'nama'     => 'Alice',
			'email'    => 'alice@example.com',
			'password' => 'password123',
		]);
		$res->assertStatus(201);
		$reg = json_decode($res->getJSON(), true);
		$token = $reg['data']['token'] ?? null;
		$this->assertNotEmpty($token);

		// Login
		$res2 = $this->post('auth/login', [
			'email'    => 'alice@example.com',
			'password' => 'password123',
		]);
		$res2->assertStatus(200);
		$log = json_decode($res2->getJSON(), true);
		$token2 = $log['data']['token'] ?? null;
		$this->assertNotEmpty($token2);

		// Me
		$res3 = $this->withHeaders(['Authorization' => 'Bearer ' . $token2])->get('auth/me');
		$res3->assertStatus(200);
		$me = json_decode($res3->getJSON(), true);
		$user = $me['data'];
		$this->assertSame('alice@example.com', $user['email']);
	}
}


