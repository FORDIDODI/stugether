<?php

namespace App\Services;

class PaginationService
{
	/**
	 * Build pagination meta payload.
	 *
	 * @param int $page
	 * @param int $perPage
	 * @param int $total
	 * @return array{page:int,per_page:int,total:int,total_pages:int}
	 */
	public function buildMeta(int $page, int $perPage, int $total): array
	{
		$page       = max(1, $page);
		$perPage    = max(1, $perPage);
		$totalPages = (int) ceil($total / $perPage);

		return [
			'page'        => $page,
			'per_page'    => $perPage,
			'total'       => $total,
			'total_pages' => $totalPages,
		];
	}
}


