<?php
/**
 * This file is part of the pomm-api-platform-bridge package.
 *
 */

namespace PommProject\ApiPlatform;

use ApiPlatform\Core\Api\ResourceClassResolverInterface;
use ApiPlatform\Core\Exception\InvalidArgumentException;
use ApiPlatform\Core\Metadata\Resource\Factory\ResourceNameCollectionFactoryInterface;
use ApiPlatform\Core\Util\ClassInfoTrait;
use PommProject\ModelManager\Model\FlexibleEntity\FlexibleEntityInterface;

/**
 * @author Mikael Paris <stood86@gmail.com>
 */
final class ResourceClassResolver implements ResourceClassResolverInterface
{
    use ClassInfoTrait;

    private $resourceNameCollectionFactory;
    private $localIsResourceClassCache = [];

    public function __construct(ResourceNameCollectionFactoryInterface $resourceNameCollectionFactory)
    {
        $this->resourceNameCollectionFactory = $resourceNameCollectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getResourceClass($value, string $resourceClass = null, bool $strict = false): string
    {
        if ($strict && null === $resourceClass) {
            throw new InvalidArgumentException('Strict checking is only possible when resource class is specified.');
        }

        $actualClass = \is_object($value) && (!$value instanceof \Traversable || $value instanceof FlexibleEntityInterface)  ? $this->getObjectClass($value) : null;

        if (null === $actualClass && null === $resourceClass) {
            throw new InvalidArgumentException('Resource type could not be determined. Resource class must be specified.');
        }

        if (null !== $actualClass && !$this->isResourceClass($actualClass)) {
            throw new InvalidArgumentException(sprintf('No resource class found for object of type "%s".', $actualClass));
        }

        if (null !== $resourceClass && !$this->isResourceClass($resourceClass)) {
            throw new InvalidArgumentException(sprintf('Specified class "%s" is not a resource class.', $resourceClass));
        }

        if ($strict && null !== $actualClass && !is_a($actualClass, $resourceClass, true)) {
            throw new InvalidArgumentException(sprintf('Object of type "%s" does not match "%s" resource class.', $actualClass, $resourceClass));
        }

        $targetClass = $actualClass ?? $resourceClass;
        $mostSpecificResourceClass = null;

        foreach ($this->resourceNameCollectionFactory->create() as $resourceClassName) {
            if (!is_a($targetClass, $resourceClassName, true)) {
                continue;
            }

            if (null === $mostSpecificResourceClass || is_subclass_of($resourceClassName, $mostSpecificResourceClass)) {
                $mostSpecificResourceClass = $resourceClassName;
            }
        }

        if (null === $mostSpecificResourceClass) {
            throw new \LogicException('Unexpected execution flow.');
        }

        return $mostSpecificResourceClass;
    }

    /**
     * {@inheritdoc}
     */
    public function isResourceClass(string $type): bool
    {
        if (isset($this->localIsResourceClassCache[$type])) {
            return $this->localIsResourceClassCache[$type];
        }

        foreach ($this->resourceNameCollectionFactory->create() as $resourceClass) {
            if (is_a($type, $resourceClass, true)) {
                return $this->localIsResourceClassCache[$type] = true;
            }
        }

        return $this->localIsResourceClassCache[$type] = false;
    }
}

