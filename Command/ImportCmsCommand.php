<?php

namespace TDK\CmsMigration\Command;

use League\Csv\Reader;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use TDK\CmsMigration\Helper\Data as CmsMigrationHelper;
use TDK\Core\Command\AbstractCommand;

/**
 * Class ImportCmsCommand
 *
 * @package TDK\CmsMigration\Command
 */
class ImportCmsCommand extends AbstractCommand
{
    /**
     * @var array
     */
    protected $blueFootEntityIdMapping = [];
    /**
     * @var \TDK\CmsMigration\Helper\Data
     */
    protected $cmsMigrationHelper;
    /**
     * @var \Gene\BlueFoot\Api\Data\EntityInterfaceFactory
     */
    protected $blueFootEntityFactory;
    /**
     * @var \Gene\BlueFoot\Api\EntityRepositoryInterface
     */
    protected $entityRepository;
    /**
     * @var \Magento\Cms\Model\BlockFactory
     */
    protected $blockFactory;
    /**
     * @var \Magento\Cms\Api\BlockRepositoryInterface
     */
    protected $blockRepository;
    /**
     * @var \Magento\Cms\Model\PageFactory
     */
    protected $pageFactory;
    /**
     * @var \Magento\Cms\Api\PageRepositoryInterface
     */
    protected $pageRepository;
    /**
     * @var \Gene\BlueFoot\Model\Stage\TemplateFactory
     */
    protected $templateFactory;
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resourceConnection;

    private $newIdMap = [];

    public function __construct(
        \Magento\Framework\App\ObjectManagerFactory $objectManagerFactory,
        \TDK\CmsMigration\Helper\Data $cmsMigrationHelper,
        \Gene\BlueFoot\Api\Data\EntityInterfaceFactory $blueFootEntityFactory,
        \Gene\BlueFoot\Api\EntityRepositoryInterface $entityRepository,
        \Magento\Cms\Model\BlockFactory $blockFactory,
        \Magento\Cms\Api\BlockRepositoryInterface $blockRepository,
        \Magento\Cms\Model\PageFactory $pageFactory,
        \Magento\Cms\Api\PageRepositoryInterface $pageRepository,
        \Gene\BlueFoot\Model\Stage\TemplateFactory $templateFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection
    ) {
        parent::__construct($objectManagerFactory);
        $this->cmsMigrationHelper = $cmsMigrationHelper;
        $this->blueFootEntityFactory = $blueFootEntityFactory;
        $this->entityRepository = $entityRepository;
        $this->blockFactory = $blockFactory;
        $this->blockRepository = $blockRepository;
        $this->pageFactory = $pageFactory;
        $this->pageRepository = $pageRepository;
        $this->templateFactory = $templateFactory;
        $this->resourceConnection = $resourceConnection;
    }

    protected function configure()
    {
        $this->setName('cms:import');
        $this->setDescription('Import CMS from CSV');

        parent::configure();
    }

    protected function _execute()
    {
        if (!$this->verifyCsvFiles()) {
            $this->writeln('Missing csv files for import');

            return;
        }

        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('<question>This will clear all Bluefoot entities. Are you sure? (y/n) </question>', false);

        if (!$helper->ask($this->getInput(), $this->getOutput(), $question)) {
            return;
        }

        $this->clearBlueFoot();
        $bluefootIds = $this->extractBlueFoot();
        $this->importBlueFoot($bluefootIds);
        $this->importBlueFootTemplates();
        $this->importCmsBlocks();
        $this->importCmsPages();
    }

    /**
     * @return bool
     */
    private function verifyCsvFiles()
    {
        $cmsDirectory = $this->cmsMigrationHelper->getCmsDirectory();

        return file_exists($cmsDirectory.CmsMigrationHelper::FILE_BLOCKS)
            && file_exists($cmsDirectory.CmsMigrationHelper::FILE_PAGES)
            && file_exists($cmsDirectory.CmsMigrationHelper::FILE_BLUEFOOT);
    }

    private function clearBlueFoot()
    {
        $this->writeln('<info>Truncating Bluefoot entity tables</info>');
        $connection = $this->resourceConnection->getConnection();

        $tables = [
            'gene_bluefoot_entity',
            'gene_bluefoot_entity_datetime',
            'gene_bluefoot_entity_decimal',
            'gene_bluefoot_entity_int',
            'gene_bluefoot_entity_text',
            'gene_bluefoot_entity_varchar',
            'gene_bluefoot_stage_template',
        ];
        $connection->query('SET FOREIGN_KEY_CHECKS=0');
        foreach ($tables as $table) {
            $tableName = $connection->getTableName($table);
            $connection->truncateTable($tableName);
        }
        $connection->query('SET FOREIGN_KEY_CHECKS=1');
    }

    private function importBlueFoot(array $bluefootIds)
    {
        $this->writeln('<info>Importing Bluefoot entities</info>');

        $this->writeln('Reading file');
        $cmsDirectory = $this->cmsMigrationHelper->getCmsDirectory();
        $reader = Reader::createFromPath($cmsDirectory.CmsMigrationHelper::FILE_BLUEFOOT);
        $header = $reader->fetchOne();
        $reader->setOffset(1);
        $rows = $reader->fetchAssoc($header);
        $rows = iterator_to_array($rows);

        $data = [];
        $lines = [];
        $progress = $this->getProgressBar($rows);
        foreach ($rows as $line => $row) {
            $progress->advance();

            $id = $row['entity_id'];
            unset($row['entity_id']);

            if (!in_array($id, $bluefootIds)) {
                continue;
            }

            $data[$id] = $row;
            $lines[$id] = $line;
        }
        $progress->finish();
        $this->writeln('');

        $this->writeln('Saving bluefoot entities to database');
        $errors = [];
        $progress = $this->getProgressBar($data);
        foreach ($data as $id => $item) {
            $progress->advance();

            try {
                /** @var \Gene\BlueFoot\Model\Entity $entity */
                $entity = $this->blueFootEntityFactory->create();
                $entity->setData($item);
                $entity = $this->entityRepository->save($entity);
                $this->newIdMap[$id] = $entity->getId();
            } catch (\Magento\Framework\Exception\CouldNotSaveException $e) {
                $errors[] = sprintf(
                    '<error>ERROR: Cannot save line %s. Entity ID: %s.</error> Data: %s',
                    $lines[$id],
                    $id,
                    json_encode($item)
                );
            }
        }
        $progress->finish();
        $this->writeln('');
        foreach ($errors as $error) {
            $this->writeln($error);
        }
        $this->writeln('Done');
    }

    private function importBlueFootTemplates()
    {
        $this->writeln('<info>Importing Bluefoot templates</info>');

        $this->writeln('Reading file');
        $cmsDirectory = $this->cmsMigrationHelper->getCmsDirectory();
        $reader = Reader::createFromPath($cmsDirectory.CmsMigrationHelper::FILE_TEMPLATES);
        $header = $reader->fetchOne();
        $reader->setOffset(1);
        $rows = $reader->fetchAssoc($header);
        $rows = iterator_to_array($rows);

        $data = [];
        $lines = [];
        $progress = $this->getProgressBar($rows);
        foreach ($rows as $line => $row) {
            $progress->advance();

            unset($row['template_id']);

            $oldStructure = $row['structure'];
            $newStructure = preg_replace_callback('/("entity_id":")(\d+)(")/', [$this, 'replace'], $oldStructure);
            $row['structure'] = $newStructure;

            $name = $row['name'];
            if (!array_key_exists($name, $data)) {
                $data[$name] = $row;
                $lines[$name] = $line;
            }
        }
        $progress->finish();
        $this->writeln('');

        $this->writeln('Saving bluefoot templates to database');
        $progress = $this->getProgressBar($data);
        foreach ($data as $name => $item) {
            $progress->advance();
            $template = $this->templateFactory->create();
            $template->addData($item);
            $template->save();
        }
        $progress->finish();
        $this->writeln('');

        $this->writeln('Done');
    }

    private function importCmsBlocks()
    {
        $this->writeln('<info>Importing cms blocks</info>');

        $this->writeln('Reading file');
        $cmsDirectory = $this->cmsMigrationHelper->getCmsDirectory();
        $reader = Reader::createFromPath($cmsDirectory.CmsMigrationHelper::FILE_BLOCKS);
        $header = $reader->fetchOne();
        $reader->setOffset(1);
        $rows = $reader->fetchAssoc($header);
        $rows = iterator_to_array($rows);

        $data = [];
        $lines = [];
        $progress = $this->getProgressBar($rows);
        foreach ($rows as $line => $row) {
            $progress->advance();

            unset($row['block_id']);

            if (strpos($row['content'], CmsMigrationHelper::NEEDLE_BLUEFOOT_CONTENT) !== false) {
                $row['content'] = preg_replace_callback(
                    '/("entityId":")(\d+)(")/',
                    [$this, 'replace'],
                    $row['content']
                );
            }

            $data[$row['identifier']] = $row;
            $lines[$row['identifier']] = $line;
        }
        $progress->finish();
        $this->writeln('');

        $this->writeln('Saving cms blocks to database');
        $progress = $this->getProgressBar($data);

        $collection = $this->blockFactory->create()->getCollection()->addFieldToFilter('identifier', ['in' => array_keys($data)]);
        $blockArray = [];

        /** @var \Magento\Cms\Model\Block $block */
        foreach ($collection as $block) {
            $blockArray[$block->getIdentifier()] = $block;
        }

        foreach ($data as $identifier => $item) {
            $progress->advance();
            try {
                /** @var \Magento\Cms\Model\Block $block */
                if (isset($blockArray[$identifier])) {
                    $block = $blockArray[$identifier];
                } else {
                    $block = $this->blockFactory->create();
                }
                $block->setStoreId(explode(',', $item['store_ids']));
                unset($item['store_ids']);
                $block->addData($item);
                $this->blockRepository->save($block);
            } catch (\Exception $e) {
                $errors[] = sprintf(
                    '<error>ERROR: Cannot save line %s.</error> Data: %s',
                    $lines[$identifier],
                    json_encode($item)
                );
            }
        }
        $progress->finish();
        $this->writeln('');

        $this->writeln('Done');
    }

    private function importCmsPages()
    {
        $this->writeln('<info>Importing cms pages</info>');

        $this->writeln('Reading file');
        $cmsDirectory = $this->cmsMigrationHelper->getCmsDirectory();
        $reader = Reader::createFromPath($cmsDirectory.CmsMigrationHelper::FILE_PAGES);
        $header = $reader->fetchOne();
        $reader->setOffset(1);
        $rows = $reader->fetchAssoc($header);
        $rows = iterator_to_array($rows);

        $data = [];
        $lines = [];
        $progress = $this->getProgressBar($rows);
        foreach ($rows as $line => $row) {
            $progress->advance();

            unset($row['page_id']);

            if (strpos($row['content'], CmsMigrationHelper::NEEDLE_BLUEFOOT_CONTENT) !== false) {
                $row['content'] = preg_replace_callback(
                    '/("entityId":")(\d+)(")/',
                    [$this, 'replace'],
                    $row['content']
                );
            }

            $data[$row['identifier']] = $row;
            $lines[$row['identifier']] = $line;
        }
        $progress->finish();
        $this->writeln('');

        $this->writeln('Saving cms pages to database');
        $progress = $this->getProgressBar($data);

        $collection = $this->pageFactory->create()->getCollection()->addFieldToFilter('identifier', ['in' => array_keys($data)]);
        $pageArray = [];

        /** @var \Magento\Cms\Model\Page $page */
        foreach ($collection as $page) {
            $pageArray[$page->getIdentifier()] = $page;
        }

        foreach ($data as $identifier => $item) {
            $progress->advance();
            try {
                /** @var \Magento\Cms\Model\Page $page */
                if (isset($pageArray[$identifier])) {
                    $page = $pageArray[$identifier];
                } else {
                    $page = $this->pageFactory->create();
                }
                $page->setStoreId(explode(',', $item['store_ids']));
                unset($item['store_ids']);
                $page->addData($item);
                $this->pageRepository->save($page);
            } catch (\Exception $e) {
                $errors[] = sprintf(
                    '<error>ERROR: Cannot save line %s.</error> Data: %s',
                    $lines[$identifier],
                    json_encode($item)
                );
            }
        }
        $progress->finish();
        $this->writeln('');

        $this->writeln('Done');
    }

    /**
     * @param array $matches
     *
     * @return string
     */
    private function replace(array $matches)
    {
        $id = $matches[2];
        if (array_key_exists($id, $this->newIdMap)) {
            return sprintf('%s%s%s', $matches[1], $this->newIdMap[$id], $matches[3]);
        } else {
            return $matches[0];
        }
    }

    /**
     * @param array $data
     *
     * @return \Symfony\Component\Console\Helper\ProgressBar
     */
    private function getProgressBar(array $data)
    {
        $progress = new ProgressBar($this->getOutput(), count($data));
        $progress->setRedrawFrequency(max((int)round(count($data) / 20), 1));
        $progress->setFormat('debug');

        return $progress;
    }

    private function extractBlueFoot()
    {
        $this->writeln('Collecting bluefoot entity');
        $bluefootIds = [];

        $cmsDirectory = $this->cmsMigrationHelper->getCmsDirectory();

        $reader = Reader::createFromPath($cmsDirectory.CmsMigrationHelper::FILE_TEMPLATES);
        $header = $reader->fetchOne();
        $reader->setOffset(1);
        $rows = $reader->fetchAssoc($header);
        $data = [];
        foreach ($rows as $row) {
            $data[$row['name']] = $row['structure'];
        }
        foreach ($data as $name => $structure) {
            preg_match_all('/("entity_id":")(\d+)(")/', $structure, $matches);
            $bluefootIds = array_merge($bluefootIds, $matches[2]);
        }

        $reader = Reader::createFromPath($cmsDirectory.CmsMigrationHelper::FILE_BLOCKS);
        $header = $reader->fetchOne();
        $reader->setOffset(1);
        $rows = $reader->fetchAssoc($header);
        $data = [];
        foreach ($rows as $line => $row) {
            $data[$row['identifier']] = $row['content'];
        }
        foreach ($data as $name => $content) {
            preg_match_all('/("entityId":")(\d+)(")/', $content, $matches);
            $bluefootIds = array_merge($bluefootIds, $matches[2]);
        }

        $reader = Reader::createFromPath($cmsDirectory.CmsMigrationHelper::FILE_PAGES);
        $header = $reader->fetchOne();
        $reader->setOffset(1);
        $rows = $reader->fetchAssoc($header);
        $data = [];
        foreach ($rows as $line => $row) {
            $data[$row['identifier']] = $row['content'];
        }
        foreach ($data as $name => $content) {
            preg_match_all('/("entityId":")(\d+)(")/', $content, $matches);
            $bluefootIds = array_merge($bluefootIds, $matches[2]);
        }

        $bluefootIds = array_unique($bluefootIds);

        return $bluefootIds;
    }
}
