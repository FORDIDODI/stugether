<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use OpenApi\Generator;

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
		CLI::write('Generating OpenAPI spec...', 'yellow');
		try {
			$schemaFile = APPPATH . 'Schemas.php';
			if (is_file($schemaFile)) {
				require_once $schemaFile;
			}
			$docsPaths = APPPATH . 'DocsPaths.php';
			if (is_file($docsPaths)) {
				require_once $docsPaths;
			}
			$paths = [
				rtrim(APPPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'Controllers',
				rtrim(APPPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'Schemas.php',
				rtrim(APPPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'DocsPaths.php',
			];
			$openapi = Generator::scan($paths);
			file_put_contents($target, $openapi->toJson());
			CLI::write('OpenAPI spec generated at ' . $target, 'green');
		} catch (\Throwable $e) {
			CLI::error('Failed to generate OpenAPI spec: ' . $e->getMessage());
		}
	}
}


