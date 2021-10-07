<?php

declare(strict_types=1);

namespace Extalion\ApiPlatformExtensionsBundle\Controller;

use ApiPlatform\Core\Api\OperationType;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGenerator;
use ApiPlatform\Core\Serializer\SerializerContextBuilderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Damian Glinkowski <damianglinkowski@extalion.com>
 */
class CustomOperation
{
    /**
     * @var \ApiPlatform\Core\Serializer\SerializerContextBuilderInterface
     */
    private $contextBuilder;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    public function __construct(
        SerializerContextBuilderInterface $contextBuilder,
        EntityManagerInterface $em
    ) {
        $this->contextBuilder = $contextBuilder;
        $this->em = $em;
    }

    public function getRequestContext(Request $request): array
    {
        $context = $this->contextBuilder->createFromRequest($request, true);
        $context['path_params'] = $this->getRequestPathParams($request);
        $context['operation_name'] = $this->getOperationName($context);

        return $context;
    }

    public function getQueryBuilder(string $resourceClass): QueryBuilder
    {
        return $this->em
            ->getRepository($resourceClass)
            ->createQueryBuilder('custom_operation')
        ;
    }

    public function getQueryNameGenerator(): QueryNameGenerator
    {
        return new QueryNameGenerator();
    }

    private function getRequestPathParams(Request $request): array
    {
        $params = [];
        $routeParams = $request->attributes->get('_route_params');

        foreach ($routeParams as $name => $value) {
            if ($name[0] !== '_') {
                $params[$name] = $value;
            }
        }

        return $params;
    }

    private function getOperationName(array $context): string
    {
        $operationTypeToName = [
            OperationType::COLLECTION => 'collection_operation_name',
            OperationType::ITEM => 'item_operation_name',
            OperationType::SUBRESOURCE => 'subresource_operation_name',
        ];

        return $context[$operationTypeToName[$context['operation_type']]];
    }
}
