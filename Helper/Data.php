<?php

namespace TDK\CmsMigration\Helper;

use Magento\Cms\Api\Data\BlockInterface;
use Magento\Cms\Api\Data\PageInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Class Data
 *
 * @package TDK\CmsMigration\Helper
 */
class Data extends AbstractHelper {
	const DIR_CMS = 'cms';

	const FILE_BLOCKS = 'blocks.csv';
	const FILE_PAGES = 'pages.csv';
	const FILE_TEMPLATES = 'templates.csv';

	const FIELD_BLOCK_STORE_IDS = 'store_ids';

	const FIELD_PAGE_STORE_IDS = 'store_ids';

	const FIELD_TEMPLATE_TEMPLATE_ID = 'template_id';
	const FIELD_TEMPLATE_NAME = 'name';
	const FIELD_TEMPLATE_STRUCTURE = 'structure';
	const FIELD_TEMPLATE_HAS_DATA = 'has_data';
	const FIELD_TEMPLATE_PREVIEW = 'preview';
	const FIELD_TEMPLATE_PINNED = 'pinned';

	/**
	 * @var \Magento\Framework\App\Filesystem\DirectoryList
	 */
	protected $directoryList;

	public function __construct(
		\Magento\Framework\App\Helper\Context $context,
		\Magento\Framework\App\Filesystem\DirectoryList $directoryList
	) {
		parent::__construct($context);
		$this->directoryList = $directoryList;
	}

	/**
	 * @return string
	 */
	public function getCmsDirectory() {
		return $this->directoryList->getPath(DirectoryList::VAR_DIR) . '/' . self::DIR_CMS . '/';
	}

	/**
	 * @param \Magento\Cms\Model\Block $block
	 *
	 * @return array
	 */
	public function getBlockDataAsArray($block) {
		return [
			BlockInterface::BLOCK_ID => $block->getId(),
			BlockInterface::IDENTIFIER => $block->getIdentifier(),
			BlockInterface::IS_ACTIVE => $block->isActive(),
			BlockInterface::TITLE => $block->getTitle(),
			BlockInterface::CONTENT => $block->getContent(),
			self::FIELD_BLOCK_STORE_IDS => implode(',', $block->getStoreId()),
		];
	}

	/**
	 * @param \Magento\Cms\Model\Page $page
	 *
	 * @return array
	 */
	public function getPageDataAsArray($page) {
		return [
			PageInterface::PAGE_ID => $page->getId(),
			PageInterface::IDENTIFIER => $page->getIdentifier(),
			PageInterface::IS_ACTIVE => $page->isActive(),
			PageInterface::TITLE => $page->getTitle(),
			PageInterface::PAGE_LAYOUT => $page->getPageLayout(),
			PageInterface::META_TITLE => $page->getMetaTitle(),
			PageInterface::META_KEYWORDS => $page->getMetaKeywords(),
			PageInterface::META_DESCRIPTION => $page->getMetaDescription(),
			PageInterface::CONTENT_HEADING => $page->getContentHeading(),
			PageInterface::CONTENT => $page->getContent(),
			PageInterface::SORT_ORDER => $page->getSortOrder(),
			PageInterface::LAYOUT_UPDATE_XML => $page->getLayoutUpdateXml(),
			PageInterface::CUSTOM_THEME => $page->getCustomTheme(),
			PageInterface::CUSTOM_ROOT_TEMPLATE => $page->getCustomRootTemplate(),
			PageInterface::CUSTOM_LAYOUT_UPDATE_XML => $page->getCustomLayoutUpdateXml(),
			PageInterface::CUSTOM_THEME_FROM => $page->getCustomThemeFrom(),
			PageInterface::CUSTOM_THEME_TO => $page->getCustomThemeTo(),
			self::FIELD_PAGE_STORE_IDS => implode(',', $page->getStoreId()),
		];
	}

}
