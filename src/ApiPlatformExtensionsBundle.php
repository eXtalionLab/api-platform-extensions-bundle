<?php

declare(strict_types=1);

namespace Extalion\ApiPlatformExtensionsBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Damian Glinkowski <damianglinkowski@extalion.com>
 */
class ApiPlatformExtensionsBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
