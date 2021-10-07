<?php

declare(strict_types=1);

namespace Extalion\ApiPlatformExtensionsBundle\Controller;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\ContextAwareQueryResultCollectionExtensionInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Damian Glinkowski <damianglinkowski@extalion.com>
 */
class CustomCollectionOperation
{
    /**
     * @var \Extalion\ApiPlatformExtensionsBundle\Controller\CustomOperation
     */
    private $customOperation;

    /**
     * @var iterable
     */
    private $extensions;

    public function __construct(
        CustomOperation $customOperation,
        iterable $extensions
    ) {
        $this->customOperation = $customOperation;
        $this->extensions = $extensions;
    }

    public function __invoke(Request $request)
    {
        $context = $this->customOperation->getRequestContext($request);
        $resourceClass = $context['resource_class'];
        $queryBuilder = $this->customOperation->getQueryBuilder($resourceClass);
        $queryNameGenerator = $this->customOperation->getQueryNameGenerator();
        $operationName = $context['operation_name'];

        foreach ($this->extensions as $extension) {
            $extension->applyToCollection(
                $queryBuilder,
                $queryNameGenerator,
                $resourceClass,
                $operationName,
                $context
            );

            if (
                $extension instanceof ContextAwareQueryResultCollectionExtensionInterface
                && $extension->supportsResult(
                    $resourceClass,
                    $operationName,
                    $context
                )
            ) {
                return $extension->getResult(
                    $queryBuilder,
                    $resourceClass,
                    $operationName,
                    $context
                );
            }
        }

        return $queryBuilder->getQuery()->getResult();
    }
}
