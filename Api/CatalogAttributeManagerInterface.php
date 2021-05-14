<?php
namespace SenzuLabs\AttributeManager\Api;

/**
 * Interface CatalogAttributeManagerInterface
 * @package SenzuLabs\AttributeManager\Api
 */
interface CatalogAttributeManagerInterface
{
    /**
     * Type constants
     */
    public const TYPE_PRODUCT_ATTRIBUTE = 'product_attribute';
    public const TYPE_PRODUCT_ATTRIBUTE_SET = 'product_attribute_set';

    /**
     * @return array
     */
    public function getTypesAllowed() : array;

    /**
     * @param array|null $types
     * @return bool
     * @throws \Exception
     */
    public function process(?array $types) : bool;
}
