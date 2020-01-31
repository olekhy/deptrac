<?php

declare(strict_types=1);

namespace SensioLabs\Deptrac\AstRunner\Resolver;

use PhpParser\Node;
use SensioLabs\Deptrac\AstRunner\AstMap\AstClassReference;
use SensioLabs\Deptrac\AstRunner\AstMap\AstDependency;
use SensioLabs\Deptrac\AstRunner\AstMap\AstFileReference;
use SensioLabs\Deptrac\AstRunner\AstMap\ClassLikeName;
use SensioLabs\Deptrac\AstRunner\AstMap\FileOccurrence;

class AnonymousClassResolver implements ClassDependencyResolver
{
    public function processNode(Node $node, AstFileReference $astFileReference, AstClassReference $astClassReference, NameScope $nameScope): void
    {
        if (!$node instanceof Node\Stmt\Class_ || null !== $node->name) {
            return;
        }

        if ($node->extends instanceof Node\Name) {
            $astClassReference->addDependency(
                AstDependency::anonymousClassExtends(
                    ClassLikeName::fromString($node->extends->toString()),
                    new FileOccurrence($astFileReference, $node->extends->getLine())
                )
            );
        }
        foreach ($node->implements as $implement) {
            $astClassReference->addDependency(
                AstDependency::anonymousClassImplements(
                    ClassLikeName::fromString($implement->toString()),
                    new FileOccurrence($astFileReference, $implement->getLine())
                )
            );
        }
    }
}
