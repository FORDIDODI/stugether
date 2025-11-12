<?php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

final class StugetherFlowTest extends CIUnitTestCase
{
	use FeatureTestTrait;

	private function registerAndLogin(string $name, string $email): string
	{
		$this->post('auth/register', ['nama' => $name, 'email' => $email, 'password' => 'password123'])->assertStatus(201);
		$res = $this->post('auth/login', ['email' => $email, 'password' => 'password123']);
		$res->assertStatus(200);
		$log = json_decode($res->getJSON(), true);
		return $log['data']['token'];
	}

	public function testForumCreateAndListScopes(): void
	{
		$token = $this->registerAndLogin('Admin User', 'admin@example.com');
		$create = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->post('forums', [
			'nama' => 'Private Forum',
			'deskripsi' => 'Test',
			'jenis_forum' => 'akademik',
			'is_public' => 0,
		]);
		$create->assertStatus(201);

		$resMine = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->get('forums?scope=mine');
		$resMine->assertStatus(200);
		$mine = json_decode($resMine->getJSON(), true);
		$this->assertGreaterThanOrEqual(1, $mine['meta']['total']);

		$resPub = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->get('forums?scope=public');
		$resPub->assertStatus(200);
		$pub = json_decode($resPub->getJSON(), true);
		$this->assertSame(0, $pub['meta']['total']);
	}

	public function testJoinForumViaKodeUndangan(): void
	{
		$tokenAdmin = $this->registerAndLogin('Admin', 'owner@example.com');
		$create = $this->withHeaders(['Authorization' => 'Bearer ' . $tokenAdmin])->post('forums', [
			'nama' => 'Joinable',
			'is_public' => 0,
		]);
		$forum = json_decode($create->getJSON(), true)['data'];
		$kode  = $forum['kode_undangan'];
		$fid   = $forum['forum_id'];

		$tokenMember = $this->registerAndLogin('Bob', 'bob@example.com');
		$join = $this->withHeaders(['Authorization' => 'Bearer ' . $tokenMember])->post("forums/{$fid}/join", [
			'kode_undangan' => $kode,
		]);
		$join->assertStatus(200);
	}

	public function testTaskFlowAndReminder(): void
	{
		$token = $this->registerAndLogin('Charlie', 'charlie@example.com');
		$createForum = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->post('forums', ['nama' => 'Tasks']);
		$forum = json_decode($createForum->getJSON(), true)['data'];
		$fid   = $forum['forum_id'];

		$createTask = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->post("forums/{$fid}/tasks", [
			'judul' => 'First Task',
			'deskripsi' => 'Do something',
		]);
		$createTask->assertStatus(201);
		$task = json_decode($createTask->getJSON(), true)['data'];

		$update = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->patch("tasks/{$task['kanban_id']}", [
			'status' => 'doing',
		]);
		$update->assertStatus(200);

		$rem1 = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->post("tasks/{$task['kanban_id']}/reminder", [
			'title' => 'Ping',
			'waktu' => gmdate('Y-m-d H:i:s', time() + 3600),
		]);
		$rem1->assertStatus(201);

		$rem2 = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->post("tasks/{$task['kanban_id']}/reminder", [
			'title' => 'Ping again',
			'waktu' => gmdate('Y-m-d H:i:s', time() + 7200),
		]);
		$rem2->assertStatus(409);
	}

	public function testDiscussionThreaded(): void
	{
		$token = $this->registerAndLogin('Dana', 'dana@example.com');
		$forum = json_decode($this->withHeaders(['Authorization' => 'Bearer ' . $token])->post('forums', ['nama' => 'Forum D'])->getJSON(), true)['data'];
		$fid = $forum['forum_id'];
		$disc = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->post("forums/{$fid}/discussions", ['isi' => 'Hello']);
		$disc->assertStatus(201);
		$did = json_decode($disc->getJSON(), true)['data']['discussion_id'];
		$rep = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->post("discussions/{$did}/replies", ['isi' => 'Reply']);
		$rep->assertStatus(201);

		$list = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->get("forums/{$fid}/discussions?threaded=true");
		$list->assertStatus(200);
		$arr = json_decode($list->getJSON(), true)['data'];
		$this->assertNotEmpty($arr[0]['children']);
	}

	public function testNoteCrudAndFilter(): void
	{
		$token = $this->registerAndLogin('Eve', 'eve@example.com');
		$forum = json_decode($this->withHeaders(['Authorization' => 'Bearer ' . $token])->post('forums', ['nama' => 'Notes'])->getJSON(), true)['data'];
		$fid = $forum['forum_id'];
		$create = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->post("forums/{$fid}/notes", [
			'judul' => 'Lecture 1',
			'kategori' => 'math',
			'mata_kuliah' => 'algebra',
		]);
		$create->assertStatus(201);
		$note = json_decode($create->getJSON(), true)['data'];

		$list = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->get("forums/{$fid}/notes?kategori=math");
		$list->assertStatus(200);
		$this->assertGreaterThanOrEqual(1, json_decode($list->getJSON(), true)['meta']['total']);

		$upd = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->patch("notes/{$note['note_id']}", ['judul' => 'Lecture 1 updated']);
		$upd->assertStatus(200);

		$del = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->delete("notes/{$note['note_id']}");
		$del->assertStatus(200);
	}

	public function testMediaUploadAndGuards(): void
	{
		$tokenAdmin = $this->registerAndLogin('Frank', 'frank@example.com');
		$forum = json_decode($this->withHeaders(['Authorization' => 'Bearer ' . $tokenAdmin])->post('forums', ['nama' => 'MediaForum', 'is_public' => 0])->getJSON(), true)['data'];
		$fid = $forum['forum_id'];

		// Non-member cannot write private forum (attempt to create task)
		$tokenOther = $this->registerAndLogin('Grace', 'grace@example.com');
		$fail = $this->withHeaders(['Authorization' => 'Bearer ' . $tokenOther])->post("forums/{$fid}/tasks", ['judul' => 'X']);
		$fail->assertStatus(403);

		// Admin uploads media with file_url
		$media = $this->withHeaders(['Authorization' => 'Bearer ' . $tokenAdmin])->post('media', [
			'forum_id' => $fid,
			'file_url' => 'https://example.com/file.pdf',
		]);
		$media->assertStatus(201);
		$mid = json_decode($media->getJSON(), true)['data']['media_id'];

		// Other user cannot delete admin's media
		$delFail = $this->withHeaders(['Authorization' => 'Bearer ' . $tokenOther])->delete("media/{$mid}");
		$delFail->assertStatus(403);

		// Non-admin cannot update forum
		$updForum = $this->withHeaders(['Authorization' => 'Bearer ' . $tokenOther])->patch("forums/{$fid}", ['nama' => 'New Name']);
		$updForum->assertStatus(403);
	}
}


