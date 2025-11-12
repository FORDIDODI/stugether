<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class GenerateDocs extends BaseCommand
{
	protected $group       = 'Stugether';
	protected $name        = 'docs:generate';
	protected $description = 'Generate OpenAPI spec to public/docs/openapi.json';

	public function run(array $params)
	{
		$target = FCPATH . 'docs/openapi.json';
		$dir    = dirname($target);
		if (! is_dir($dir)) {
			mkdir($dir, 0775, true);
		}
		$cmd = escapeshellcmd(PHP_BINARY) . ' ' . ROOTPATH . 'vendor/bin/openapi app -o ' . escapeshellarg($target);
		CLI::write('Generating OpenAPI spec...', 'yellow');
		exec($cmd, $out, $code);
		if ($code !== 0) {
			CLI::error('Failed to generate OpenAPI spec.');
			return;
		}
		CLI::write('OpenAPI spec generated at ' . $target, 'green');
	}
}


