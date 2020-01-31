<?php

declare(strict_types=1);

namespace SensioLabs\Deptrac\Collector;

use SensioLabs\Deptrac\AstRunner\AstMap;
use SensioLabs\Deptrac\AstRunner\AstMap\AstClassReference;

class ClassNameCollector implements CollectorInterface
{
    public function getType(): string
    {
        return 'className';
    }

    public function satisfy(
        array $configuration,
        AstClassReference $astClassReference,
        AstMap $astMap,
        Registry $collectorRegistry
    ): bool {
        return $astClassReference->getClassName()->match($this->getPattern($configuration));
    }

    private function getPattern(array $configuration): string
    {
        if (!isset($configuration['regex'])) {
            throw new \LogicException('ClassNameCollector needs the regex configuration.');
        }

        return '/'.$configuration['regex'].'/i';
    }
}
