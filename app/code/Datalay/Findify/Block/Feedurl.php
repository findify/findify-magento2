<?php
namespace Datalay\Findify\Block;

//class Feedurl extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;


class Feedurl extends Field
{
    protected $_template = 'Datalay_Findify::feedurl.phtml';
    //protected $storeManager;
    //protected $scopeConfig;
        
    public function __construct(
        //\Magento\Store\Model\StoreManagerInterface $storeManager,
        //\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        Context $context,
        array $data = []
    ) {
        //$this->storeManager = $storeManager;
        //$this->scopeConfig = $scopeConfig;
                
        parent::__construct($context, $data);
    }

    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }

/*    public function getAjaxUrl()
    {
        return $this->getUrl('datalay_findify/system_config/collect');
    }
*/
/*    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            [
                'id' => 'collect_button',
                'label' => __('Collect Data'),
            ]
        );

        return $button->toHtml();
    }
*/
    public function getUrlText()
    {
        $baseUrl = $this->_storeManager->getStore()->getBaseUrl();

        $request = $this->_request;
        $storeId = (int) $request->getParam('store', 0);

        $configfilename = $this->_scopeConfig->getValue('attributes/feedinfo/feedfilename', \Magento\Store\Model\ScopeInterface::SCOPE_STORE); 
        $filename = str_replace("/", "", $configfilename);
        if(empty($filename)){
            $filename = 'jsonl_feed-'.$storeId;
        }
        $file = 'pub/media/findify/'.$filename.'.gz';

        $url = $baseUrl.$file;
        
        return $url;
    }
}
?>
