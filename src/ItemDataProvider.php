<?php

/*
 * This file is part of the API Platform project.
 *
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PommProject\ApiPlatform;

use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use ApiPlatform\Core\Exception\ResourceClassNotSupportedException;
use PommProject\Foundation\Pomm;
use PommProject\ModelManager\Model\FlexibleEntity\FlexibleEntityInterface;
use ReflectionClass;

class ItemDataProvider implements ItemDataProviderInterface, RestrictedDataProviderInterface
{
    private $pomm;

    public function __construct(Pomm $pomm)
    {
        $this->pomm = $pomm;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        $proxyClass = new ReflectionClass($resourceClass);

        return $proxyClass->implementsInterface(FlexibleEntityInterface::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = [])
    {
        if (isset($context['session:name'])) {
            $session = $this->pomm->getSession($context['session:name']);
        } else {
            $session = $this->pomm->getDefaultSession();
        }

        if (isset($context['model:name'])) {
            $modelName = $context['model:name'];
        } else {
            $modelName = "${resourceClass}Model";
        }

        if (!class_exists($modelName)) {
                throw new ResourceClassNotSupportedException();
        }

        $model = $session->getModel($modelName);
        $primaryKeys = $model->getStructure()
            ->getPrimaryKey();

        return $model->findByPk([$primaryKeys[0] => $id]);
    }
}
