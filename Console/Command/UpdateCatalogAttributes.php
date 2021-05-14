<?php
namespace SenzuLabs\AttributeManager\Console\Command;

use SenzuLabs\AttributeManager\Api\CatalogAttributeManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class UpdateCatalogAttributes
 *
 * @package SenzuLabs\AttributeManager\Console\Command
 */
class UpdateCatalogAttributes extends Command
{
    private const INPUT_VALUE_TYPE_ALL = 'all';

    /**
     * Command option for the types to process
     */
    private const OPTION_TYPE = 'type';

    /**
     * Command shortcut option for the types to process
     */
    private const OPTION_TYPE_SHORTCUT = 't';

    /**
     * Command default name
     */
    protected static $defaultName = 'senzulabs:catalogattribute:update';

    /**
     * @var CatalogAttributeManagerInterface
     */
    private $catalogAttributeManager;

    /**
     * @param CatalogAttributeManagerInterface $catalogAttributeManager
     * @param string|null $name
     */
    public function __construct(
        CatalogAttributeManagerInterface $catalogAttributeManager,
        string $name = null
    ) {
        $this->catalogAttributeManager = $catalogAttributeManager;

        $name = is_null($name) ? self::$defaultName : $name;
        parent::__construct($name);
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setDescription('Updates catalog attribute entity types given');

        $this->addOption(
            self::OPTION_TYPE,
            self::OPTION_TYPE_SHORTCUT,
            InputOption::VALUE_REQUIRED,
            'Comma-separated list of entity types to process ('
            . implode(',', $this->catalogAttributeManager->getTypesAllowed()) . ')'
        );

        parent::configure();
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $entityTypesToProcess = $this->getEntityTypesToProcess($input);
            if(!$entityTypesToProcess) {
                $output->writeln('<error>No entity type found to process.</error>');
                return;
            }

            $output->writeln(sprintf(
                '<info>Catalog entity type(s) found to process: %s</info>',
                implode(',', $entityTypesToProcess)
            ));

            $types = in_array(self::INPUT_VALUE_TYPE_ALL, $entityTypesToProcess) ? null : $entityTypesToProcess;
            if ($this->catalogAttributeManager->process($types)) {
                $output->writeln('<info>Catalog entities types have been processed</info>');
            } else {
                $output->writeln('<error>Could not process the catalog entities types</error>');
            }
        } catch (\Throwable $t) {
            $output->writeln(sprintf('<error>%s</error>', $t->getMessage()));
            $output->writeln($t->getTraceAsString());
        }
    }

    /**
     * @param InputInterface $input
     * @return array
     */
    private function getEntityTypesToProcess(InputInterface $input) : array
    {
        $entityTypes = [];
        if(strlen($input->getOption(self::OPTION_TYPE))) {
            $entityTypes = array_unique(preg_split('/[\;\,\s\|]/', $input->getOption(self::OPTION_TYPE),
                NULL, PREG_SPLIT_NO_EMPTY));
        }

        if ((count($entityTypes) > 1) && in_array(self::INPUT_VALUE_TYPE_ALL, $entityTypes)) {
            $entityTypes = array_diff($entityTypes, [self::INPUT_VALUE_TYPE_ALL]);
        }

        return $entityTypes;
    }
}
