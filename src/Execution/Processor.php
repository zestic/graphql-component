<?php

namespace Zestic\GraphQL\Execution;

use App\Service\OperationMapping;
use Common\Communique\Factory\CommuniqueFactory;
use Zestic\GraphQL\Execution\Context\ExecutionContext;
use GraphQLMiddleware\Validation\ValidatableFieldInterface;
use Youshido\GraphQL\Execution\Processor as BaseProcessor;
use Youshido\GraphQL\Field\FieldInterface;
use Youshido\GraphQL\Parser\Ast\Field as AstField;
use Youshido\GraphQL\Parser\Ast\Interfaces\FieldInterface as AstFieldInterface;
use Youshido\GraphQL\Parser\Ast\Mutation;
use Youshido\GraphQL\Parser\Ast\Query as AstQuery;
use Youshido\GraphQL\Schema\AbstractSchema;

class Processor extends BaseProcessor
{
    /** @var CommuniqueFactory */
    private $communiqueFactory;
    /** @var OperationMapping */
    private $operationMapping;

    public function __construct(CommuniqueFactory $communiqueFactory, OperationMapping $operationMapping, AbstractSchema $schema)
    {
        $this->communiqueFactory = $communiqueFactory;
        $this->executionContext = new ExecutionContext($schema);
        $this->operationMapping = $operationMapping;

        parent::__construct($this->executionContext->getSchema());
    }

    protected function doResolve(FieldInterface $field, AstFieldInterface $ast, $parentValue = null)
    {
        /** @var AstQuery|AstField $ast */
        $arguments = $this->parseArgumentsValues($field, $ast);
        $astFields = $ast instanceof AstQuery ? $ast->getFields() : [];
        $resolveInfo = $this->createResolveInfo($field, $astFields);

        //allow userland validation for mutation args
        if ($ast instanceof Mutation) {
            if ($field instanceof ValidatableFieldInterface) {

                $field->validate($arguments, $resolveInfo);

                if ($this->getExecutionContext()->hasErrors()) {
                    return null;
                }
            }
        }

        $commmunique = $this->communiqueFactory->createFromGraphQL($arguments, $resolveInfo);

        $reply = $this->operationMapping->handle($commmunique);

        // todo : proper response from reply
    }
}
