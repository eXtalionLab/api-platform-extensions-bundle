<?php

declare(strict_types=1);

namespace Extalion\ApiPlatformExtensionsBundle\Controller;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\ContextAwareQueryResultItemExtensionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @author Damian Glinkowski <damianglinkowski@extalion.com>
 */
class CustomItemOperation
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
        $identifiers = $context['path_params'];

        foreach ($this->extensions as $extension) {
            $extension->applyToItem(
                $queryBuilder,
                $queryNameGenerator,
                $resourceClass,
                $identifiers,
                $operationName,
                $context
            );

            if (
                $extension instanceof ContextAwareQueryResultItemExtensionInterface
                && $extension->supportsResult(
                    $resourceClass,
                    $operationName,
                    $context
                )
            ) {
                $result = $extension->getResult(
                    $queryBuilder,
                    $resourceClass,
                    $operationName,
                    $context
                );

                return $this->getOneOrThrowNotFound($result);
            }
        }

        $result = $queryBuilder->getQuery()->getOneOrNullResult();

        return $this->getOneOrThrowNotFound($result);
    }

    private function getOneOrThrowNotFound($result)
    {
        if ($result === null) {
            throw new NotFoundHttpException('Not found');
        }

        return $result;
    }
}
