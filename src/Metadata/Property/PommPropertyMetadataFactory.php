<?php
/**
 * This file is part of the pomm-api-platform-bridge package.
 *
 */

namespace PommProject\ApiPlatform\Metadata\Property;


use ApiPlatform\Core\Exception\PropertyNotFoundException;
use ApiPlatform\Core\Exception\ResourceClassNotFoundException;
use ApiPlatform\Core\Metadata\Property\Factory\PropertyMetadataFactoryInterface;
use ApiPlatform\Core\Metadata\Property\PropertyMetadata;
use PommProject\Foundation\Pomm;

/**
 * @author Mikael Paris <stood86@gmail.com>
 */
final class PommPropertyMetadataFactory implements PropertyMetadataFactoryInterface
{
    private $pomm;

    private $decorated;

    public function __construct(Pomm $pomm, PropertyMetadataFactoryInterface $decorated)
    {
        $this->pomm = $pomm;
        $this->decorated = $decorated;
    }

    /**
     * Creates a property metadata.
     *
     * @throws PropertyNotFoundException
     */
    public function create(string $resourceClass, string $property, array $options = []): PropertyMetadata
    {
        $propertyMetadata = $this->decorated->create($resourceClass, $property, $options);

        if (null !== $propertyMetadata->isIdentifier()) {
            return $propertyMetadata;
        }

        $session = $this->pomm->getDefaultSession();
        $modelName = "${resourceClass}Model";

        if (!class_exists($modelName)) {
            return $propertyMetadata;
        }

        $model = $session->getModel($modelName);
        $fieldNames = $model->getStructure()
            ->getFieldNames();

        $primaryKeys = $model->getStructure()
            ->getPrimaryKey();

        if (in_array($property, $primaryKeys)) {
            $propertyMetadata = $propertyMetadata->withIdentifier(true);
        }

        if (null === $propertyMetadata->isIdentifier()) {
            $propertyMetadata = $propertyMetadata->withIdentifier(false);
        }

        if (in_array($property, $fieldNames)) {
            $propertyMetadata = $propertyMetadata->withReadable(true);
            $propertyMetadata = $propertyMetadata->withWritable(true);
        }

        return $propertyMetadata;
    }
}
