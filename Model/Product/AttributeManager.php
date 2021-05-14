<?php
namespace SenzuLabs\AttributeManager\Model\Product;

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Store\Model\Store;
use Psr\Log\LoggerInterface;
use Zend_Validate_Exception;

/**
 * Class AttributeManager
 * @package SenzuLabs\AttributeManager\Model\Product
 */
class AttributeManager
{
    public const SLABS_OPTION_TEXT_SWATCH_VALUES = 'slabs_swatch_values_text';
    private const LOGGER_PREFIX = '[slabs_product_attribute_manager] ';

    /**
     * @var string[]
     */
    private array $attributes;
    private ModuleDataSetupInterface $moduleDataSetup;
    private EavSetup $eavSetup;
    private ProductAttributeRepositoryInterface $productAttributeRepository;
    private LoggerInterface $logger;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     * @param ProductAttributeRepositoryInterface $productAttributeRepositoryInterface
     * @param LoggerInterface $logger
     * @param string[] $attributes
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory,
        ProductAttributeRepositoryInterface $productAttributeRepository,
        LoggerInterface $logger,
        $attributes = []
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->productAttributeRepository = $productAttributeRepository;
        $this->logger = $logger;
        $this->attributes = $attributes;
        $this->eavSetup = $eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
    }

    /**
     * @param array|null $data
     * @return bool
     * @throws \Exception
     */
    public function process(?array $data): bool
    {
        $this->logger->info(self::LOGGER_PREFIX
            . 'process started with \'' . implode(',', $data ?? ['null']) . '\'');
        $this->moduleDataSetup->getConnection()->startSetup();

        $attributes = $this->getAttributesToProcess($data);
        foreach ($attributes as $attributeCode => $attributeDefinition) {
            $this->processAttribute($attributeCode, $attributeDefinition);
        }

        $this->moduleDataSetup->getConnection()->endSetup();
        $this->logger->info(self::LOGGER_PREFIX . 'process ended');

        return true;
    }

    /**
     * @param array|null $data
     * @return string[][]
     */
    public function getAttributesToProcess(?array $data): array
    {
        $result = $this->attributes;
        if (is_array($data)) {
            $result = array_intersect_key($result, $data);
        }

        return $result;
    }

    /**
     * @param string $attributeCode
     * @param array $attributeDefinition
     * @return void
     * @throws LocalizedException
     * @throws Zend_Validate_Exception
     */
    private function processAttribute(
        string $attributeCode,
        array $attributeDefinition
    ): void {
        $this->logger->info(self::LOGGER_PREFIX ."creating attribute: '{$attributeCode}'");

        $attributeData = $attributeDefinition['schema'];
        $this->eavSetup->addAttribute(
            Product::ENTITY,
            $attributeCode,
            $attributeData
        );

        $textSwatchValues = $attributeData['option'][self::SLABS_OPTION_TEXT_SWATCH_VALUES] ?? null;
        if ($textSwatchValues) {
            $this->processTextSwatches($attributeCode, $textSwatchValues);
        }

        $this->logger->info(self::LOGGER_PREFIX ."attribute created: '{$attributeCode}'");
    }

    /**
     * @param string $attributeCode
     * @param array $textSwatchValues
     * @throws LocalizedException
     * @return void
     */
    private function processTextSwatches(
        string $attributeCode,
        array $textSwatchValues
    ): void {
        $this->logger->info(self::LOGGER_PREFIX ."creating text swatches: '{$attributeCode}'");

        $attribute = $this->productAttributeRepository->get($attributeCode);
        if (!$attribute || !$attribute->getAttributeId()) {
            return;
        }

        if ($optionValues = $this->extractAttributeOptionValues($textSwatchValues)) {
            $this->eavSetup->addAttributeOption([
                'attribute_id' => $attribute->getAttributeId(),
                'values' => $optionValues
            ]);
        }

        $attributeOptions = $attribute->getSource()->getAllOptions() ? : [];
        if ($optionSwatchText = $this->extractAttributeOptionSwatchText($attributeOptions, $textSwatchValues)) {
            $attribute->setData('update_product_preview_image', '0');
            $attribute->setData('use_product_image_for_swatch', '0');
            $attribute->setData('swatch_input_type', 'text');
            $attribute->setData('swatchtext', $optionSwatchText);
            $this->productAttributeRepository->save($attribute);
        }

        $this->logger->info(self::LOGGER_PREFIX ."text swatches created: '{$attributeCode}'");
    }

    /**
     * @param array $optionsData
     * @return array
     */
    private function extractAttributeOptionValues(array $optionsData)
    {
        $optionValues = [];
        foreach ($optionsData as $key => $value) {
            $optionValues[$key] = is_array($value) ? $value['label'] : $value;
        }
        return $optionValues;
    }

    /**
     * @param AttributeOptionInterface[] $attributeOptions
     * @param array $optionsData
     * @return array
     */
    private function extractAttributeOptionSwatchText(array $attributeOptions, array $optionsData)
    {
        $result = [];

        foreach ($attributeOptions as $attributeOption) {
            $optionId = $attributeOption['value'];
            $label = $attributeOption['label'];
            if ($optionId && strlen($label)) {
                $swatchText = $this->getSwatchTextByOptionLabel($optionsData, $label);
                $swatchText = strlen($swatchText) ? $swatchText : $label;
                $result['value'][$optionId][Store::DEFAULT_STORE_ID] = $swatchText;
            }
        }

        return $result;
    }

    /**
     * @param array $optionsData
     * @param string $searchLabel
     * @return array
     */
    private function getSwatchTextByOptionLabel(array $optionsData, string $searchLabel)
    {
        $result = '';
        foreach ($optionsData as $key => $value) {
            $optionLabel = is_array($value) ? $value['label'] : $value;
            if ($optionLabel === $searchLabel) {
                $result = is_array($value) ? $value['text'] : $value;
                break;
            }
        }

        return $result;
    }
}
