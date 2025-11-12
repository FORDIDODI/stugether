<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class Docs extends Controller
{
	public function index()
	{
		$path = FCPATH . 'docs/index.html';
		if (! is_file($path)) {
			return redirect()->to('/');
		}
		return $this->response->setHeader('Content-Type', 'text/html')->setBody(file_get_contents($path));
	}
}


