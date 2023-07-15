<?php

namespace App\Service;

class ArrayService
{
	public function getMaxLen(array $input): int {
		return \max(
			\array_map(
				static fn($v) => \mb_strlen((string) $v), 
				$input
			)
		);
	}
}