<?php

declare(strict_types=1);

namespace SensioLabs\Deptrac\Dependency;

use SensioLabs\Deptrac\AstRunner\AstMap\ClassLikeName;

class Result
{
    /** @var array<string, Dependency[]> */
    private $dependencies = [];

    /** @var array<string, InheritDependency[]> */
    private $inheritDependencies = [];

    public function addDependency(Dependency $dependency): self
    {
        $classLikeName = (string) $dependency->getClassA();
        if (!isset($this->dependencies[$classLikeName])) {
            $this->dependencies[$classLikeName] = [];
        }

        $this->dependencies[$classLikeName][] = $dependency;

        return $this;
    }

    public function addInheritDependency(InheritDependency $dependency): self
    {
        $classLikeName = (string) $dependency->getClassA();
        if (!isset($this->inheritDependencies[$classLikeName])) {
            $this->inheritDependencies[$classLikeName] = [];
        }

        $this->inheritDependencies[$classLikeName][] = $dependency;

        return $this;
    }

    /**
     * @return Dependency[]
     */
    public function getDependenciesByClass(ClassLikeName $className): array
    {
        return $this->dependencies[(string) $className] ?? [];
    }

    /**
     * @return DependencyInterface[]
     */
    public function getDependenciesAndInheritDependencies(): array
    {
        $buffer = [];

        foreach ($this->dependencies as $deps) {
            foreach ($deps as $dependency) {
                $buffer[] = $dependency;
            }
        }
        foreach ($this->inheritDependencies as $deps) {
            foreach ($deps as $dependency) {
                $buffer[] = $dependency;
            }
        }

        return $buffer;
    }
}
