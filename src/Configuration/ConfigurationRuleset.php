<?php

declare(strict_types=1);

namespace SensioLabs\Deptrac\Configuration;

class ConfigurationRuleset
{
    /** @var array<string, string[]> */
    private $layerMap;

    /**
     * @param array<string, string[]> $arr
     */
    public static function fromArray(array $arr): self
    {
        return new static($arr);
    }

    /**
     * @param array<string, string[]> $layerMap
     */
    private function __construct(array $layerMap)
    {
        $this->layerMap = $layerMap;
    }

    /**
     * @return string[]
     */
    public function getAllowedDependencies(string $layerName): array
    {
        return $this->layerMap[$layerName] ?? [];
    }
}
