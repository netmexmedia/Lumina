<?php

declare(strict_types=1);

namespace Netmex\Lumina;

use GraphQL\Executor\ExecutionResult;
use GraphQL\GraphQL;
use Netmex\Lumina\Contracts\ContextBuilderInterface;
use Netmex\Lumina\Http\Request\GraphQLRequest;
use Netmex\Lumina\Schema\AST\ASTMutator;
use Netmex\Lumina\Schema\Compiler\FieldResolverCompiler;
use Netmex\Lumina\Schema\Compiler\IntentCompiler;
use Netmex\Lumina\Schema\Source\SchemaSourceRegistry;

readonly class Kernel
{
    private SchemaSourceRegistry $schemaSourceRegistry;
    private IntentCompiler $intentCompiler;
    private ASTMutator $astMutator;
    private FieldResolverCompiler $fieldResolverCompiler;
    private ContextBuilderInterface $contextBuilder;

    public function __construct(SchemaSourceRegistry $schemaSourceRegistry, IntentCompiler $intentCompiler, ASTMutator $astMutator, FieldResolverCompiler $fieldResolverCompiler, ContextBuilderInterface $contextBuilder) {
        $this->schemaSourceRegistry = $schemaSourceRegistry;
        $this->astMutator = $astMutator;
        $this->intentCompiler = $intentCompiler;
        $this->fieldResolverCompiler = $fieldResolverCompiler;

        $this->contextBuilder = $contextBuilder;
    }

    public function execute(GraphQLRequest $request): ExecutionResult
    {
        $this->schemaSourceRegistry->buildDocumentFromSdl();
        $this->intentCompiler->compile();
        $this->astMutator->mutate();
        $this->fieldResolverCompiler->compile();

        $result = GraphQL::executeQuery(
            schema: $this->schemaSourceRegistry->getSchema(),
            source: $request->query,
            rootValue: null,
            contextValue: $this->contextBuilder->build(),
            variableValues: $request->variables,
            operationName: $request->operationName,
            fieldResolver: null,
            validationRules: null
        );

        return $result;
    }
}