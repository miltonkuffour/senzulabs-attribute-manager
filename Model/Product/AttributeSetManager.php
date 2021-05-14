<?php
namespace SenzuLabs\AttributeManager\Model\Product;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Setup\CategorySetup;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Eav\Model\Entity\Attribute\Set as AttributeSet;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Psr\Log\LoggerInterface;

/**
 * Class AttributeSetManager
 * @package SenzuLabs\AttributeManager\Model\Product
 */
class AttributeSetManager
{
    private const LOGGER_PREFIX = '[slabs_product_attribute_set_manager] ';

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var CategorySetupFactory
     */
    private $categorySetupFactory;

    /**
     * @var AttributeSetFactory
     */
    private $attributeSetFactory;

    /**
     * @var AttributeSetRepositoryInterface
     */
    private $attributeSetRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string[]
     */
    private $attributeSets;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CategorySetupFactory $categorySetupFactory
     * @param AttributeSetFactory $attributeSetFactory
     * @param AttributeSetRepositoryInterface $attributeSetRepository
     * @param LoggerInterface $logger
     * @param string[] $attributeSets
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CategorySetupFactory $categorySetupFactory,
        AttributeSetFactory $attributeSetFactory,
        AttributeSetRepositoryInterface $attributeSetRepository,
        LoggerInterface $logger,
        $attributeSets = []
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->categorySetupFactory = $categorySetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->attributeSetRepository = $attributeSetRepository;
        $this->logger = $logger;
        $this->attributeSets = $attributeSets;
    }

    /**
     * @param array|null $data
     * @return bool
     * @throws \Exception
     */
    public function process(?array $data): bool
    {
        $this->logger->info(self::LOGGER_PREFIX . 'process started with \'' . implode($data ?? ['null']) . '\'');
        $this->moduleDataSetup->getConnection()->startSetup();

        /** @var CategorySetup $categorySetup */
        $categorySetup = $this->categorySetupFactory->create(['setup' => $this->moduleDataSetup]);
        $attributeSets = $this->getAttributeSetsToProcess($data);
        foreach($attributeSets as $attributeSetData) {
            $attributeSetName = $attributeSetData['label'];
            if (!$categorySetup->getAttributeSet(Product::ENTITY, $attributeSetName)) {
                $this->createAttributeSet($categorySetup, $attributeSetName);
            }
            if (!empty($attributeSetData['groups'])) {
                foreach ($attributeSetData['groups'] as $group) {
                    $this->addAttributesToGroup($categorySetup, $attributeSetName, $group);
                }
            }
        }

        $this->moduleDataSetup->getConnection()->endSetup();
        $this->logger->info(self::LOGGER_PREFIX . 'process ended');

        return true;
    }

    /**
     * @param array|null $data
     * @return string[]
     */
    public function getAttributeSetsToProcess(?array $data): array
    {
        $result = $this->attributeSets;
        if (is_array($data)) {
            $result = array_intersect_key($result, $data);
        }

        return $result;
    }

    /**
     * @param CategorySetup $categorySetup
     * @param string $attributeSetName
     * @return void
     * @throws LocalizedException
     */
    private function createAttributeSet(CategorySetup $categorySetup, string $attributeSetName): void
    {
        $this->logger->info(self::LOGGER_PREFIX ."creating attribute set: '{$attributeSetName}'");

        /** @var AttributeSet $attributeSet */
        $attributeSet = $this->attributeSetFactory->create();
        $productEntityTypeId = $categorySetup->getEntityTypeId(Product::ENTITY);
        $attributeSet->setData([
            'attribute_set_name' => $attributeSetName,
            'entity_type_id' => $productEntityTypeId
        ]);
        $attributeSet->validate();
        $this->attributeSetRepository->save($attributeSet);

        $defaultAttributeSetId = $categorySetup->getDefaultAttributeSetId($productEntityTypeId);
        $attributeSet->initFromSkeleton($defaultAttributeSetId);
        $this->attributeSetRepository->save($attributeSet);

        $this->logger->info(self::LOGGER_PREFIX ."attribute set created: '{$attributeSetName}'");
    }

    /**
     * @param CategorySetup $categorySetup
     * @param string $attributeSetName
     * @param array $groups
     * @return void
     * @throws LocalizedException
     */
    private function addAttributesToGroup(
        CategorySetup $categorySetup,
        string $attributeSetName,
        array $group
    ): void {
        $groupName = $group['label'];
        $groupSortOrder = $group['sortOrder'] ?? null;
        $categorySetup->addAttributeGroup(Product::ENTITY, $attributeSetName, $groupName, $groupSortOrder);

        foreach ($group['attributes'] as $attributeCode => $value) {
            $this->logger->info(self::LOGGER_PREFIX
                . "adding attribute to group: '{$attributeSetName}, $groupName, $attributeCode'");

            $categorySetup->addAttributeToSet(
                Product::ENTITY,
                $attributeSetName,
                $groupName,
                $attributeCode
            );

            $this->logger->info(self::LOGGER_PREFIX
                . "attribute added to group: '{$attributeSetName}, $groupName, $attributeCode'");
        }
    }
}
