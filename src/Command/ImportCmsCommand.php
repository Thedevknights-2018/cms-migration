<?php

namespace TDK\CmsMigration\Command;

use League\Csv\Reader;
use Symfony\Component\Console\Helper\ProgressBar;
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
     * @var \TDK\CmsMigration\Helper\Data
     */
    protected $cmsMigrationHelper;
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
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resourceConnection;

    private $newIdMap = [];

    public function __construct(
        \Magento\Framework\App\ObjectManagerFactory $objectManagerFactory,
        \TDK\CmsMigration\Helper\Data $cmsMigrationHelper,
        \Magento\Cms\Model\BlockFactory $blockFactory,
        \Magento\Cms\Api\BlockRepositoryInterface $blockRepository,
        \Magento\Cms\Model\PageFactory $pageFactory,
        \Magento\Cms\Api\PageRepositoryInterface $pageRepository,
        \Magento\Framework\App\ResourceConnection $resourceConnection
    ) {
        parent::__construct($objectManagerFactory);
        $this->cmsMigrationHelper = $cmsMigrationHelper;
        $this->blockFactory = $blockFactory;
        $this->blockRepository = $blockRepository;
        $this->pageFactory = $pageFactory;
        $this->pageRepository = $pageRepository;
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
            && file_exists($cmsDirectory.CmsMigrationHelper::FILE_PAGES);
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

}
