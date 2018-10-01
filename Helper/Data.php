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
class Data extends AbstractHelper
{
    const DIR_CMS = 'cms';

    const FILE_BLOCKS = 'blocks.csv';
    const FILE_PAGES = 'pages.csv';
    const FILE_BLUEFOOT = 'bluefoot.csv';
    const FILE_TEMPLATES = 'templates.csv';

    const FIELD_BLOCK_STORE_IDS = 'store_ids';

    const FIELD_PAGE_STORE_IDS = 'store_ids';

    const FIELD_BLUEFOOT_ENTITY_ID = 'entity_id';
    const FIELD_BLUEFOOT_ENTITY_TYPE_ID = 'entity_type_id';
    const FIELD_BLUEFOOT_ATTRIBUTE_SET_ID = 'attribute_set_id';
    const FIELD_BLUEFOOT_ACCORDION_ITEMS = 'accordion_items';
    const FIELD_BLUEFOOT_ADVANCED_SLIDER_ITEMS = 'advanced_slider_items';
    const FIELD_BLUEFOOT_ALT_TAG = 'alt_tag';
    const FIELD_BLUEFOOT_ANCHOR_ID = 'anchor_id';
    const FIELD_BLUEFOOT_APP_DISPLAY = 'app_display';
    const FIELD_BLUEFOOT_APP_ENTITY_COLLECTION = 'app_entity_collection';
    const FIELD_BLUEFOOT_APP_ENTITY_COUNT = 'app_entity_count';
    const FIELD_BLUEFOOT_APP_ENTITY_ID = 'app_entity_id';
    const FIELD_BLUEFOOT_AUTOPLAY = 'autoplay';
    const FIELD_BLUEFOOT_AUTOPLAY_SPEED = 'autoplay_speed';
    const FIELD_BLUEFOOT_BACKGROUND_IMAGE = 'background_image';
    const FIELD_BLUEFOOT_BLOCK_ID = 'block_id';
    const FIELD_BLUEFOOT_BUTTON_ITEMS = 'button_items';
    const FIELD_BLUEFOOT_BUTTON_TEXT = 'button_text';
    const FIELD_BLUEFOOT_CATEGORY_ID = 'category_id';
    const FIELD_BLUEFOOT_COLOR = 'color';
    const FIELD_BLUEFOOT_CSS_CLASSES = 'css_classes';
    const FIELD_BLUEFOOT_CUSTOM_DESIGN = 'custom_design';
    const FIELD_BLUEFOOT_CUSTOM_LAYOUT_UPDATE = 'custom_layout_update';
    const FIELD_BLUEFOOT_CUSTOM_TEMPLATE = 'custom_template';
    const FIELD_BLUEFOOT_FADE = 'fade';
    const FIELD_BLUEFOOT_HAS_LIGHTBOX = 'has_lightbox';
    const FIELD_BLUEFOOT_HAS_OVERLAY = 'has_overlay';
    const FIELD_BLUEFOOT_HEADING_TYPE = 'heading_type';
    const FIELD_BLUEFOOT_HIDE_OUT_OF_STOCK = 'hide_out_of_stock';
    const FIELD_BLUEFOOT_HR_HEIGHT = 'hr_height';
    const FIELD_BLUEFOOT_HR_WIDTH = 'hr_width';
    const FIELD_BLUEFOOT_HTML = 'html';
    const FIELD_BLUEFOOT_IDENTIFIER = 'identifier';
    const FIELD_BLUEFOOT_IMAGE = 'image';
    const FIELD_BLUEFOOT_IS_ACTIVE = 'is_active';
    const FIELD_BLUEFOOT_IS_INFINITE = 'is_infinite';
    const FIELD_BLUEFOOT_LABEL = 'label';
    const FIELD_BLUEFOOT_LINK_TEXT = 'link_text';
    const FIELD_BLUEFOOT_LINK_URL = 'link_url';
    const FIELD_BLUEFOOT_MAIN_CONTENT = 'main_content';
    const FIELD_BLUEFOOT_MAP = 'map';
    const FIELD_BLUEFOOT_MAP_HEIGHT = 'map_height';
    const FIELD_BLUEFOOT_MAP_WIDTH = 'map_width';
    const FIELD_BLUEFOOT_META_DESCRIPTION = 'meta_description';
    const FIELD_BLUEFOOT_META_KEYWORDS = 'meta_keywords';
    const FIELD_BLUEFOOT_META_TITLE = 'meta_title';
    const FIELD_BLUEFOOT_MOBILE_IMAGE = 'mobile_image';
    const FIELD_BLUEFOOT_OPEN_ON_LOAD = 'open_on_load';
    const FIELD_BLUEFOOT_PAGE_LAYOUT = 'page_layout';
    const FIELD_BLUEFOOT_PLACEHOLDER = 'placeholder';
    const FIELD_BLUEFOOT_PRODUCT_COUNT = 'product_count';
    const FIELD_BLUEFOOT_PRODUCT_DISPLAY = 'product_display';
    const FIELD_BLUEFOOT_PRODUCT_ID = 'product_id';
    const FIELD_BLUEFOOT_SHOW_ARROWS = 'show_arrows';
    const FIELD_BLUEFOOT_SHOW_CAPTION = 'show_caption';
    const FIELD_BLUEFOOT_SHOW_DOTS = 'show_dots';
    const FIELD_BLUEFOOT_SLIDER_ADVANCED_SETTINGS = 'slider_advanced_settings';
    const FIELD_BLUEFOOT_SLIDER_ITEMS = 'slider_items';
    const FIELD_BLUEFOOT_SLIDES_TO_SCROLL = 'slides_to_scroll';
    const FIELD_BLUEFOOT_SLIDES_TO_SHOW = 'slides_to_show';
    const FIELD_BLUEFOOT_TABS_ITEMS = 'tabs_items';
    const FIELD_BLUEFOOT_TARGET_BLANK = 'target_blank';
    const FIELD_BLUEFOOT_TEXTAREA = 'textarea';
    const FIELD_BLUEFOOT_TITLE = 'title';
    const FIELD_BLUEFOOT_TITLE_TAG = 'title_tag';
    const FIELD_BLUEFOOT_VIDEO_HEIGHT = 'video_height';
    const FIELD_BLUEFOOT_VIDEO_URL = 'video_url';
    const FIELD_BLUEFOOT_VIDEO_WIDTH = 'video_width';

    const FIELD_TEMPLATE_TEMPLATE_ID = 'template_id';
    const FIELD_TEMPLATE_NAME = 'name';
    const FIELD_TEMPLATE_STRUCTURE = 'structure';
    const FIELD_TEMPLATE_HAS_DATA = 'has_data';
    const FIELD_TEMPLATE_PREVIEW = 'preview';
    const FIELD_TEMPLATE_PINNED = 'pinned';

    const MAPPING_BLUEFOOT_OLD_ENTITY_ID = 'old_entity_id';
    const MAPPING_BLUEFOOT_NEW_ENTITY_ID = 'new_entity_id';

    const NEEDLE_BLUEFOOT_CONTENT = '<!--GENE_BLUEFOOT';

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
    public function getCmsDirectory()
    {
        return $this->directoryList->getPath(DirectoryList::VAR_DIR).'/'.self::DIR_CMS.'/';
    }

    /**
     * @param \Magento\Cms\Model\Block $block
     *
     * @return array
     */
    public function getBlockDataAsArray($block)
    {
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
    public function getPageDataAsArray($page)
    {
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

    /**
     * @param \Gene\BlueFoot\Model\Entity $entity
     *
     * @return array
     */
    public function getBlueFootDataAsArray($entity)
    {
        return [
            self::FIELD_BLUEFOOT_ENTITY_ID => $entity->getData(self::FIELD_BLUEFOOT_ENTITY_ID),
            self::FIELD_BLUEFOOT_ENTITY_TYPE_ID => $entity->getData(self::FIELD_BLUEFOOT_ENTITY_TYPE_ID),
            self::FIELD_BLUEFOOT_ATTRIBUTE_SET_ID => $entity->getData(self::FIELD_BLUEFOOT_ATTRIBUTE_SET_ID),
            self::FIELD_BLUEFOOT_ACCORDION_ITEMS => $entity->getData(self::FIELD_BLUEFOOT_ACCORDION_ITEMS),
            self::FIELD_BLUEFOOT_ADVANCED_SLIDER_ITEMS => $entity->getData(self::FIELD_BLUEFOOT_ADVANCED_SLIDER_ITEMS),
            self::FIELD_BLUEFOOT_ALT_TAG => $entity->getData(self::FIELD_BLUEFOOT_ALT_TAG),
            self::FIELD_BLUEFOOT_ANCHOR_ID => $entity->getData(self::FIELD_BLUEFOOT_ANCHOR_ID),
            self::FIELD_BLUEFOOT_APP_DISPLAY => $entity->getData(self::FIELD_BLUEFOOT_APP_DISPLAY),
            self::FIELD_BLUEFOOT_APP_ENTITY_COLLECTION => $entity->getData(self::FIELD_BLUEFOOT_APP_ENTITY_COLLECTION),
            self::FIELD_BLUEFOOT_APP_ENTITY_COUNT => $entity->getData(self::FIELD_BLUEFOOT_APP_ENTITY_COUNT),
            self::FIELD_BLUEFOOT_APP_ENTITY_ID => $entity->getData(self::FIELD_BLUEFOOT_APP_ENTITY_ID),
            self::FIELD_BLUEFOOT_AUTOPLAY => $entity->getData(self::FIELD_BLUEFOOT_AUTOPLAY),
            self::FIELD_BLUEFOOT_AUTOPLAY_SPEED => $entity->getData(self::FIELD_BLUEFOOT_AUTOPLAY_SPEED),
            self::FIELD_BLUEFOOT_BACKGROUND_IMAGE => $entity->getData(self::FIELD_BLUEFOOT_BACKGROUND_IMAGE),
            self::FIELD_BLUEFOOT_BLOCK_ID => $entity->getData(self::FIELD_BLUEFOOT_BLOCK_ID),
            self::FIELD_BLUEFOOT_BUTTON_ITEMS => $entity->getData(self::FIELD_BLUEFOOT_BUTTON_ITEMS),
            self::FIELD_BLUEFOOT_BUTTON_TEXT => $entity->getData(self::FIELD_BLUEFOOT_BUTTON_TEXT),
            self::FIELD_BLUEFOOT_CATEGORY_ID => $entity->getData(self::FIELD_BLUEFOOT_CATEGORY_ID),
            self::FIELD_BLUEFOOT_COLOR => $entity->getData(self::FIELD_BLUEFOOT_COLOR),
            self::FIELD_BLUEFOOT_CSS_CLASSES => $entity->getData(self::FIELD_BLUEFOOT_CSS_CLASSES),
            self::FIELD_BLUEFOOT_CUSTOM_DESIGN => $entity->getData(self::FIELD_BLUEFOOT_CUSTOM_DESIGN),
            self::FIELD_BLUEFOOT_CUSTOM_LAYOUT_UPDATE => $entity->getData(self::FIELD_BLUEFOOT_CUSTOM_LAYOUT_UPDATE),
            self::FIELD_BLUEFOOT_CUSTOM_TEMPLATE => $entity->getData(self::FIELD_BLUEFOOT_CUSTOM_TEMPLATE),
            self::FIELD_BLUEFOOT_FADE => $entity->getData(self::FIELD_BLUEFOOT_FADE),
            self::FIELD_BLUEFOOT_HAS_LIGHTBOX => $entity->getData(self::FIELD_BLUEFOOT_HAS_LIGHTBOX),
            self::FIELD_BLUEFOOT_HAS_OVERLAY => $entity->getData(self::FIELD_BLUEFOOT_HAS_OVERLAY),
            self::FIELD_BLUEFOOT_HEADING_TYPE => $entity->getData(self::FIELD_BLUEFOOT_HEADING_TYPE),
            self::FIELD_BLUEFOOT_HIDE_OUT_OF_STOCK => $entity->getData(self::FIELD_BLUEFOOT_HIDE_OUT_OF_STOCK),
            self::FIELD_BLUEFOOT_HR_HEIGHT => $entity->getData(self::FIELD_BLUEFOOT_HR_HEIGHT),
            self::FIELD_BLUEFOOT_HR_WIDTH => $entity->getData(self::FIELD_BLUEFOOT_HR_WIDTH),
            self::FIELD_BLUEFOOT_HTML => $entity->getData(self::FIELD_BLUEFOOT_HTML),
            self::FIELD_BLUEFOOT_IDENTIFIER => $entity->getData(self::FIELD_BLUEFOOT_IDENTIFIER),
            self::FIELD_BLUEFOOT_IMAGE => $entity->getData(self::FIELD_BLUEFOOT_IMAGE),
            self::FIELD_BLUEFOOT_IS_ACTIVE => $entity->getData(self::FIELD_BLUEFOOT_IS_ACTIVE),
            self::FIELD_BLUEFOOT_IS_INFINITE => $entity->getData(self::FIELD_BLUEFOOT_IS_INFINITE),
            self::FIELD_BLUEFOOT_LABEL => $entity->getData(self::FIELD_BLUEFOOT_LABEL),
            self::FIELD_BLUEFOOT_LINK_TEXT => $entity->getData(self::FIELD_BLUEFOOT_LINK_TEXT),
            self::FIELD_BLUEFOOT_LINK_URL => $entity->getData(self::FIELD_BLUEFOOT_LINK_URL),
            self::FIELD_BLUEFOOT_MAIN_CONTENT => $entity->getData(self::FIELD_BLUEFOOT_MAIN_CONTENT),
            self::FIELD_BLUEFOOT_MAP => $entity->getData(self::FIELD_BLUEFOOT_MAP),
            self::FIELD_BLUEFOOT_MAP_HEIGHT => $entity->getData(self::FIELD_BLUEFOOT_MAP_HEIGHT),
            self::FIELD_BLUEFOOT_MAP_WIDTH => $entity->getData(self::FIELD_BLUEFOOT_MAP_WIDTH),
            self::FIELD_BLUEFOOT_META_DESCRIPTION => $entity->getData(self::FIELD_BLUEFOOT_META_DESCRIPTION),
            self::FIELD_BLUEFOOT_META_KEYWORDS => $entity->getData(self::FIELD_BLUEFOOT_META_KEYWORDS),
            self::FIELD_BLUEFOOT_META_TITLE => $entity->getData(self::FIELD_BLUEFOOT_META_TITLE),
            self::FIELD_BLUEFOOT_MOBILE_IMAGE => $entity->getData(self::FIELD_BLUEFOOT_MOBILE_IMAGE),
            self::FIELD_BLUEFOOT_OPEN_ON_LOAD => $entity->getData(self::FIELD_BLUEFOOT_OPEN_ON_LOAD),
            self::FIELD_BLUEFOOT_PAGE_LAYOUT => $entity->getData(self::FIELD_BLUEFOOT_PAGE_LAYOUT),
            self::FIELD_BLUEFOOT_PLACEHOLDER => $entity->getData(self::FIELD_BLUEFOOT_PLACEHOLDER),
            self::FIELD_BLUEFOOT_PRODUCT_COUNT => $entity->getData(self::FIELD_BLUEFOOT_PRODUCT_COUNT),
            self::FIELD_BLUEFOOT_PRODUCT_DISPLAY => $entity->getData(self::FIELD_BLUEFOOT_PRODUCT_DISPLAY),
            self::FIELD_BLUEFOOT_PRODUCT_ID => $entity->getData(self::FIELD_BLUEFOOT_PRODUCT_ID),
            self::FIELD_BLUEFOOT_SHOW_ARROWS => $entity->getData(self::FIELD_BLUEFOOT_SHOW_ARROWS),
            self::FIELD_BLUEFOOT_SHOW_CAPTION => $entity->getData(self::FIELD_BLUEFOOT_SHOW_CAPTION),
            self::FIELD_BLUEFOOT_SHOW_DOTS => $entity->getData(self::FIELD_BLUEFOOT_SHOW_DOTS),
            self::FIELD_BLUEFOOT_SLIDER_ADVANCED_SETTINGS => $entity->getData(self::FIELD_BLUEFOOT_SLIDER_ADVANCED_SETTINGS),
            self::FIELD_BLUEFOOT_SLIDER_ITEMS => $entity->getData(self::FIELD_BLUEFOOT_SLIDER_ITEMS),
            self::FIELD_BLUEFOOT_SLIDES_TO_SCROLL => $entity->getData(self::FIELD_BLUEFOOT_SLIDES_TO_SCROLL),
            self::FIELD_BLUEFOOT_SLIDES_TO_SHOW => $entity->getData(self::FIELD_BLUEFOOT_SLIDES_TO_SHOW),
            self::FIELD_BLUEFOOT_TABS_ITEMS => $entity->getData(self::FIELD_BLUEFOOT_TABS_ITEMS),
            self::FIELD_BLUEFOOT_TARGET_BLANK => $entity->getData(self::FIELD_BLUEFOOT_TARGET_BLANK),
            self::FIELD_BLUEFOOT_TEXTAREA => $entity->getData(self::FIELD_BLUEFOOT_TEXTAREA),
            self::FIELD_BLUEFOOT_TITLE => $entity->getData(self::FIELD_BLUEFOOT_TITLE),
            self::FIELD_BLUEFOOT_TITLE_TAG => $entity->getData(self::FIELD_BLUEFOOT_TITLE_TAG),
            self::FIELD_BLUEFOOT_VIDEO_HEIGHT => $entity->getData(self::FIELD_BLUEFOOT_VIDEO_HEIGHT),
            self::FIELD_BLUEFOOT_VIDEO_URL => $entity->getData(self::FIELD_BLUEFOOT_VIDEO_URL),
            self::FIELD_BLUEFOOT_VIDEO_WIDTH => $entity->getData(self::FIELD_BLUEFOOT_VIDEO_WIDTH),
        ];
    }

    /**
     * @param \Gene\BlueFoot\Model\Stage\Template $template
     *
     * @return array
     */
    public function getTemplateDataAsArray($template)
    {
        return [
            self::FIELD_TEMPLATE_TEMPLATE_ID => $template->getData(self::FIELD_TEMPLATE_TEMPLATE_ID),
            self::FIELD_TEMPLATE_NAME => $template->getData(self::FIELD_TEMPLATE_NAME),
            self::FIELD_TEMPLATE_STRUCTURE => $template->getData(self::FIELD_TEMPLATE_STRUCTURE),
            self::FIELD_TEMPLATE_HAS_DATA => $template->getData(self::FIELD_TEMPLATE_HAS_DATA),
            self::FIELD_TEMPLATE_PREVIEW => $template->getData(self::FIELD_TEMPLATE_PREVIEW),
            self::FIELD_TEMPLATE_PINNED => $template->getData(self::FIELD_TEMPLATE_PINNED),
        ];
    }
}
