<?php

declare(strict_types=1);

namespace SensioLabs\Deptrac\AstRunner\Resolver;

use PhpParser\Node;
use SensioLabs\Deptrac\AstRunner\AstMap\ClassReferenceBuilder;

class AnonymousClassResolver implements ClassDependencyResolver
{
    public function processNode(Node $node, ClassReferenceBuilder $classReferenceBuilder, NameScope $nameScope): void
    {
        if (!$node instanceof Node\Stmt\Class_ || null !== $node->name) {
            return;
        }

        if ($node->extends instanceof Node\Name) {
            $classReferenceBuilder->anonymousClassExtends($node->extends->toString(), $node->extends->getLine());
        }
        foreach ($node->implements as $implement) {
            $classReferenceBuilder->anonymousClassImplements($implement->toString(), $implement->getLine());
        }
    }
}
