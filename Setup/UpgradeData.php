<?php
 
namespace Findify\Findify\Setup;
 
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
 
class UpgradeData implements UpgradeDataInterface
{
    protected $pageFactory;
 
    public function __construct(
        \Magento\Cms\Model\PageFactory $pageFactory
    ) {
        $this->pageFactory = $pageFactory;
    }
 
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
 
        if (version_compare($context->getVersion(), '1.0.0') < 0) {
            $page = $this->pageFactory->create();
            $page->setTitle('search')
                ->setIdentifier('search')
                ->setIsActive(true)
                ->setPageLayout('1column')
                ->setStores(array(0)) //available for all store views
                ->setContent('<div data-findify-attr="findify-search-results"></div>')
                ->save();
        }
 
        $setup->endSetup();
    }

}
