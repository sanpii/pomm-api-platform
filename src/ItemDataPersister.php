<?php
/**
 * This file is part of the pomm-api-platform-bridge package.
 *
 */

namespace PommProject\ApiPlatform;


use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use PommProject\Foundation\Pomm;
use PommProject\ModelManager\Model\FlexibleEntity\FlexibleEntityInterface;
use ReflectionClass;

/**
 * @author Mikael Paris <stood86@gmail.com>
 */
final class ItemDataPersister implements ContextAwareDataPersisterInterface
{
    /**
     * @var Pomm
     */
    protected $pomm;

    public function __construct(Pomm $pomm)
    {
        $this->pomm = $pomm;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($data, array $context = []): bool
    {
        $proxyClass = new ReflectionClass($data);

        return $proxyClass->implementsInterface(FlexibleEntityInterface::class);
    }

    /**
     * {@inheritdoc}
     */
    public function persist($data, array $context = [])
    {
        $model = $this->getModel($data, $context);

        if (null === $model) {
            return $data;
        }

        if (isset($context['collection_operation_name']) && 'post' === $context['collection_operation_name']) {
            $model->insertOne($data);
        } elseif (isset($context['item_operation_name']) && 'put' === $context['item_operation_name']) {
            $fields = array_keys($data->fields());
            $model->updateOne($data, $fields);
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($data, array $context = [])
    {
        $model = $this->getModel($data, $context);

        if (null === $model) {
            return $data;
        }

        if (isset($context['item_operation_name']) && 'delete' === $context['item_operation_name']) {
            $model->deleteOne($data);
        }

        return $data;
    }

    protected function getModel($data, $context)
    {
        $proxyClass = new ReflectionClass($data);
        $class = $proxyClass->getName();

        if (isset($context['session:name'])) {
            $session = $this->pomm->getSession($context['session:name']);
        } else {
            $session = $this->pomm->getDefaultSession();
        }

        if (isset($context['model:name'])) {
            $model_name = $context['model:name'];
        } else {
            $model_name = "${class}Model";
        }

        if (!class_exists($model_name)) {
            return;
        }

        return $session->getModel($model_name);
    }
}
