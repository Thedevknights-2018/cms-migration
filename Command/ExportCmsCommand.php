<?php

namespace TDK\CmsMigration\Command;

use League\Csv\Writer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TDK\CmsMigration\Helper\Data as CmsMigrationHelper;

/**
 * Class ExportCmsCommand
 *
 * @package TDK\CmsMigration\Command
 */
class ExportCmsCommand extends Command
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
     * @var \Magento\Cms\Model\PageFactory
     */
    protected $pageFactory;
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    public function __construct(
        \TDK\CmsMigration\Helper\Data $cmsMigrationHelper,
        \Magento\Cms\Model\BlockFactory $blockFactory,
        \Magento\Cms\Model\PageFactory $pageFactory,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        parent::__construct();
        $this->cmsMigrationHelper = $cmsMigrationHelper;
        $this->blockFactory = $blockFactory;
        $this->pageFactory = $pageFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    protected function configure()
    {
        $this->setName('cms:export');
        $this->setDescription('Export CMS blocks, pages to CSV');

        parent::configure();
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return null|int null or 0 if everything went fine, or an error code
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln($this->getName().' started');
        $time = microtime(true);

        $this->_execute($input, $output);

        $output->writeln($this->getName().' finished. Elapsed time: '.round(microtime(true) - $time, 2).'s'."\n");

        return null;
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    private function _execute(InputInterface $input, OutputInterface $output)
    {
        $this->exportCmsBlocks($input, $output);
        $this->exportCmsPages($input, $output);
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    private function exportCmsBlocks(InputInterface $input, OutputInterface $output)
    {
        $cmsDirectory = $this->cmsMigrationHelper->getCmsDirectory();
        $writer = Writer::createFromPath($cmsDirectory.CmsMigrationHelper::FILE_BLOCKS, 'w');
        $hasHeader = false;
        $blocks = $this->blockFactory->create()->getCollection();

        /** @var \Magento\Cms\Model\Block $block */
        foreach ($blocks as $block) {
            $output->write(sprintf('Exporting block id %s... ', $block->getId()));
            $blockData = $this->cmsMigrationHelper->getBlockDataAsArray($block);

            if ($hasHeader === false) {
                $writer->insertOne(array_keys($blockData));
                $hasHeader = true;
            }

            $writer->insertOne($blockData);
            $output->writeln('Done');
        }
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    private function exportCmsPages(InputInterface $input, OutputInterface $output)
    {
        $cmsDirectory = $this->cmsMigrationHelper->getCmsDirectory();
        $writer = Writer::createFromPath($cmsDirectory.CmsMigrationHelper::FILE_PAGES, 'w');
        $hasHeader = false;
        $pages = $this->pageFactory->create()->getCollection();

        /** @var \Magento\Cms\Model\Page $page */
        foreach ($pages as $page) {
            $output->write(sprintf('Exporting page id %s... ', $page->getId()));
            $pageData = $this->cmsMigrationHelper->getPageDataAsArray($page);

            if ($hasHeader === false) {
                $writer->insertOne(array_keys($pageData));
                $hasHeader = true;
            }

            $writer->insertOne($pageData);
            $output->writeln('Done');
        }
    }
}
