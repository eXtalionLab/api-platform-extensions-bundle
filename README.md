# api-platform extensions

Installation
============

Make sure Composer is installed globally, as explained in the
[installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Applications that use Symfony Flex
----------------------------------

Open a command console, enter your project directory and execute:

```console
$ composer require extalion/api-platform-extensions-bundle
```

Applications that don't use Symfony Flex
----------------------------------------

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require extalion/api-platform-extensions-bundle
```

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file of your project:

```php
// config/bundles.php

return [
    // ...
    Extalion\ApiPlatformExtensionsBundle\ApiPlatformExtensionsBundle::class => ['all' => true],
];
```

Usage
=====

### Custom data provider

If you want to add data provider and keep injected api-platform extensions, add
custom operation to entity

```php
<?php
// src/Entity/Book.php

use ApiPlatform\Core\Annotation\ApiResource;
use Extalion\ApiPlatformExtensionsBundle\Controller\CustomCollectionOperation;
use Extalion\ApiPlatformExtensionsBundle\Controller\CustomItemOperation;

#[ApiResource(
  collectionOperations: [
    'get_custom_collection' => [
      'method' => 'GET',
      'path' => '/custom_books',
      'controller' => CustomCollectionOperation::class,
    ],
  ],
  itemOperations: [
    'get_custom_item' => [
      'method' => 'GET',
      'path' => '/custom_books/{id}',
      'controller' => CustomItemOperation::class,
      'read' => false,
    ],
  ]
)]
class Book
{
    // ...
}
```

and create doctrine extension

```php
<?php
// src/Doctrine/Extension/Operations/GetCustomBook.php

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\ContextAwareQueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\Book;
use Doctrine\ORM\QueryBuilder;

final class GetCustomBook implements ContextAwareQueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        string $operationName = null,
        array $context = []
    ): void {
        if (
            $resourceClass === Book::class
            && $operationName === 'get_custom_collection'
        ) {
            $book = $queryBuilder->getRootAliases()[0];
            $queryBuilder->andWhere("{$book}.customField = :book_custom_field");
            $queryBuilder->setParameter('book_custom_field', true);
        }
    }

    public function applyToItem(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        array $identifiers,
        string $operationName = null,
        array $context = []
    ): void {
        if (
            $resourceClass === Book::class
            && $operationName === 'get_custom_item'
        ) {
            $id = $identifiers['id'];
            $book = $queryBuilder->getRootAliases()[0];
            $queryBuilder->andWhere("{$book}.id = :book_id");
            $queryBuilder->andWhere("{$book}.customField = :book_custom_field");
            $queryBuilder->setParameter('book_id', $id);
            $queryBuilder->setParameter('book_custom_field', true);
        }
    }
}
```
