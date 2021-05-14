<?php
namespace SenzuLabs\AttributeManager\Model;

use InvalidArgumentException;
use SenzuLabs\AttributeManager\Api\CatalogAttributeManagerInterface;
use SenzuLabs\AttributeManager\Model\Product\AttributeSetManager as ProductAttributeSetManager;
use SenzuLabs\AttributeManager\Model\Product\AttributeManager as ProductAttributeManager;

/**
 * Class CatalogAttributeManager
 * @package SenzuLabs\AttributeManager\Model
 */
class CatalogAttributeManager implements CatalogAttributeManagerInterface
{
    /**
     * @var ProductAttributeManager
     */
    private $productAttributeManager;

    /**
     * @var ProductAttributeSetManager
     */
    private $productAttributeSetManager;

    /**
     * @param ProductAttributeManager $productAttributeManager
     * @param ProductAttributeSetManager $productAttributeSetManager
     */
    public function __construct(
        ProductAttributeManager $productAttributeManager,
        ProductAttributeSetManager $productAttributeSetManager
    ) {
        $this->productAttributeManager = $productAttributeManager;
        $this->productAttributeSetManager = $productAttributeSetManager;
    }

    /**
     * @inheritDoc
     */
    public function getTypesAllowed(): array
    {
        return [
            self::TYPE_PRODUCT_ATTRIBUTE,
            self::TYPE_PRODUCT_ATTRIBUTE_SET
        ];
    }

    /**
     * @inheritDoc
     */
    public function process(?array $types): bool
    {
        $types = $types ?? $this->getTypesAllowed();
        foreach ($types as $key => $value) {
            $type = is_string($key) ? $key : $value;
            $data = is_string($key) ? $value : null;
            switch ($type) {
                case self::TYPE_PRODUCT_ATTRIBUTE:
                    $this->processProductAttributes($data);
                    break;
                case self::TYPE_PRODUCT_ATTRIBUTE_SET:
                    $this->processProductAttributeSets($data);
                    break;
                default:
                    throw new InvalidArgumentException(__('The given type [%1] is not supported.', $type));
            }
        }

        return true;
    }

    /**
     * @param array|null $codesToProcess
     * @return bool
     * @throws \Exception
     */
    public function processProductAttributes(?array $codesToProcess) : bool
    {
        return $this->productAttributeManager->process($codesToProcess);
    }

    /**
     * @param array|null $namesToProcess
     * @return bool
     * @throws \Exception
     */
    public function processProductAttributeSets(?array $namesToProcess = null) : bool
    {
        return $this->productAttributeSetManager->process($namesToProcess);
    }
}
