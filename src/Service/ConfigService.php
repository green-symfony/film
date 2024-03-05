<?php

namespace App\Service;

use GS\Service\Service\ConfigService as GSConfigService;
use Symfony\Component\OptionsResolver\{
    Options,
    OptionsResolver
};

class ConfigService extends GSConfigService
{
	protected function configureConfigOptions(
		string $uniqPackId,
		OptionsResolver $resolver,
		array $inputData,
	): void {
	}
}
