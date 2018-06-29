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

use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\Exception\ResourceClassNotSupportedException;
use PommProject\Foundation\Pomm;
use PommProject\Foundation\Where;
use PommProject\ModelManager\Model\Model;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class CollectionDataProvider implements CollectionDataProviderInterface
{
    private $pomm;
    private $requestStack;
    private $pagination;
    private $order;

    public function __construct(
        Pomm $pomm,
        RequestStack $requestStack,
        array $pagination,
        array $order
    ) {
        $this->pomm = $pomm;
        $this->requestStack = $requestStack;
        $this->pagination = $pagination;
        $this->order = $order;
    }

    /**
     * {@inheritdoc}
     */
    public function getCollection(string $resourceClass, string $operationName = null)
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
            return;
        }

        $modelName = "${resourceClass}Model";
        if (!class_exists($modelName)) {
            throw new ResourceClassNotSupportedException();
        }

        $session = $this->pomm->getDefaultSession();
        $model = $session->getModel($modelName);
        $paginator = $model->paginateFindWhere(
            $this->getWhere($request, $model),
            $this->pagination['items_per_page'],
            $this->getCurrentPage($request),
            $this->getOrderSuffix($request)
        );

        return new Paginator($paginator);
    }

    private function getCurrentPage(Request $request): int
    {
        return $request->query->get($this->pagination['items_per_page_parameter_name'], 1);
    }

    private function getOrderSuffix(Request $request): string
    {

        $properties = $request->query->get($this->order['order_parameter_name']);
        if ($properties === null) {
            return '';
        }

        $suffix = [];
        foreach ($properties as $property => $order) {
            $suffix[] = "$property $order";
        }

        return 'order by ' . implode(',', $suffix);
    }

    private function getWhere(Request $request, Model $model): Where
    {
        $properties = $model->getStructure()
            ->getFieldNames();

        $where = new Where();

        foreach ($properties as $property) {
            if ($request->query->has($property)) {
                $value = $request->query->get($property);
                $where->andWhere("$property = \$*", [$value]);
            }
        }

        return $where;
    }
}
