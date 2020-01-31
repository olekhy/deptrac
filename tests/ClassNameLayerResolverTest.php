<?php

declare(strict_types=1);

namespace Tests\SensioLabs\Deptrac;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use SensioLabs\Deptrac\AstRunner\AstMap;
use SensioLabs\Deptrac\AstRunner\AstMap\AstClassReference;
use SensioLabs\Deptrac\AstRunner\AstMap\ClassLikeName;
use SensioLabs\Deptrac\ClassNameLayerResolver;
use SensioLabs\Deptrac\Collector\CollectorInterface;
use SensioLabs\Deptrac\Collector\Registry;
use SensioLabs\Deptrac\Configuration\Configuration;
use SensioLabs\Deptrac\Configuration\ConfigurationLayer;

class ClassNameLayerResolverTest extends TestCase
{
    private function getCollector(bool $return)
    {
        $collector = $this->prophesize(CollectorInterface::class);
        $collector->satisfy(
            Argument::type('array'),
            Argument::type(AstClassReference::class),
            Argument::type(AstMap::class),
            Argument::type(Registry::class)
        )->willReturn($return);

        return $collector->reveal();
    }

    public function provideGetLayersByClassName(): iterable
    {
        yield [
            true,
            true,
            true,
            ['LayerA', 'LayerB'],
        ];

        yield [
            true,
            false,
            true,
            ['LayerA', 'LayerB'],
        ];

        yield [
            false,
            false,
            true,
            ['LayerB'],
        ];

        yield [
            true,
            true,
            false,
            ['LayerA', 'LayerB'],
        ];

        yield [
            true,
            false,
            false,
            ['LayerA'],
        ];

        yield [
            false,
            false,
            false,
            [],
        ];
    }

    /**
     * @dataProvider provideGetLayersByClassName
     */
    public function testGetLayersByClassName(bool $collectA, bool $collectB1, bool $collectB2, array $expectedLayers): void
    {
        $configuration = $this->prophesize(Configuration::class);
        $configuration->getLayers()->willReturn([
            ConfigurationLayer::fromArray([
                'name' => 'LayerA',
                'collectors' => [
                    ['type' => 'CollectorA'],
                ],
            ]),
            ConfigurationLayer::fromArray([
                'name' => 'LayerB',
                'collectors' => [
                    ['type' => 'CollectorB1'],
                    ['type' => 'CollectorB2'],
                ],
            ]),
        ]);

        $astMap = $this->prophesize(AstMap::class);
        $collectorRegistry = $this->prophesize(Registry::class);
        $collectorRegistry->getCollector('CollectorA')->willReturn(
            $this->getCollector($collectA)
        );
        $collectorRegistry->getCollector('CollectorB1')->willReturn(
            $this->getCollector($collectB1)
        );
        $collectorRegistry->getCollector('CollectorB2')->willReturn(
            $this->getCollector($collectB2)
        );

        $resolver = new ClassNameLayerResolver(
            $configuration->reveal(),
            $astMap->reveal(),
            $collectorRegistry->reveal()
        );

        static::assertEquals(
            $expectedLayers,
            $resolver->getLayersByClassName(ClassLikeName::fromString('classA'))
        );
    }
}
