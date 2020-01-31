<?php

declare(strict_types=1);

namespace SensioLabs\Deptrac\AstRunner\Resolver;

use PhpParser\Comment\Doc;
use PhpParser\Node;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;
use SensioLabs\Deptrac\AstRunner\AstMap\ClassReferenceBuilder;

class AnnotationDependencyResolver implements ClassDependencyResolver
{
    private $lexer;
    private $docParser;

    /**
     * @var TypeResolver
     */
    private $typeResolver;

    public function __construct(TypeResolver $typeResolver)
    {
        $this->lexer = new Lexer();
        $this->docParser = new PhpDocParser(new TypeParser(), new ConstExprParser());
        $this->typeResolver = $typeResolver;
    }

    public function processNode(Node $node, ClassReferenceBuilder $classReferenceBuilder, NameScope $nameScope): void
    {
        if (!$node instanceof Node\Stmt\Property
            && !$node instanceof Node\Expr\Variable
            && !$node instanceof Node\Stmt\ClassMethod
        ) {
            return;
        }

        $docComment = $node->getDocComment();
        if (!$docComment instanceof Doc) {
            return;
        }

        $tokens = new TokenIterator($this->lexer->tokenize($docComment->getText()));
        $docNode = $this->docParser->parse($tokens);

        foreach ($docNode->getParamTagValues() as $tag) {
            $types = $this->typeResolver->resolveType($tag->type, $nameScope);

            foreach ($types as $type) {
                $classReferenceBuilder->parameter($type, $docComment->getLine());
            }
        }

        foreach ($docNode->getVarTagValues() as $tag) {
            $types = $this->typeResolver->resolveType($tag->type, $nameScope);

            foreach ($types as $type) {
                $classReferenceBuilder->variable($type, $docComment->getLine());
            }
        }

        foreach ($docNode->getReturnTagValues() as $tag) {
            $types = $this->typeResolver->resolveType($tag->type, $nameScope);

            foreach ($types as $type) {
                $classReferenceBuilder->returnType($type, $docComment->getLine());
            }
        }

        foreach ($docNode->getThrowsTagValues() as $tag) {
            $types = $this->typeResolver->resolveType($tag->type, $nameScope);

            foreach ($types as $type) {
                $classReferenceBuilder->throwStatement($type, $docComment->getLine());
            }
        }
    }
}
