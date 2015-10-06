<?php
require_once 'abstract.php';
 
class BlueVisionTec_Shell_GoogleShopping extends Mage_Shell_Abstract
{

    /**
     * @var int
     */
    protected $_storeId = null;
    /**
     * @var int
     */
    protected $categoryId = null;
    /**
     * @var int
     */
    protected $_productIds = null;
    /**
     * @var string
     */
    protected $_action = null;
    

    /**
     * constructor
     */
    public function __construct() {
        parent::__construct();

        // unset time limit
        set_time_limit(0);     
        
        if($this->getArg('action')) {
           $this->_action = $this->getArg('action');
        }
        if($this->getArg('productids')) {
           $this->_productIds = explode(",",$this->getArg('productids'));
        }
        if($this->getArg('categoryid')) {
           $this->_categoryId = $this->getArg('categoryid');
        }
        if($this->getArg('store')) {
           $this->_storeId = $this->getArg('store');
        }
        if($this->getArg('storeid')) {
           $this->_storeId = $this->getArg('storeid');
        }
        
    }
    
    /**
     * constructor
     */
    public function run() {
        
        
        switch($this->_action) {
            case 'getcategory':
                return $this->getCategory();
                break;
            case 'setcategory':
                return $this->setCategory();
                break;
            default:
                print $this->usageHelp();
                return false;
        }
        
    }
    
    /**
     * print category of products
     */
    protected function getCategory() {

        if($this->_storeId) {
            Mage::app()->setCurrentStore($this->_storeId);
        }
        $productCollection = Mage::getModel('catalog/product')
                            ->getCollection()
                            ->addAttributeToSelect('google_shopping_category');
        
        if($this->_productIds) {
            $productCollection->addAttributeToFilter('entity_id', array('in' => $this->_productIds));
        }
         if($this->_storeId) {
            $productCollection->addStoreFilter($this->_storeId);
        }
        
        foreach($productCollection as $product) {

            print $product->getId().";".Mage::getModel('catalog/product')->load($product->getId())->getGoogleShoppingCategory()."\n";
        }
    }
    /**
     * set GoogleShopping category ids
     */
    protected function setCategory() {
         if(!$this->_productIds || !$this->_categoryId) {
            print $this->usageHelp();
            return false;
         }
         
         foreach($this->_productIds as $productId) {
            if($this->_storeId) {
                Mage::app()->setCurrentStore($this->_storeId);
            }
            $product = Mage::getModel('catalog/product')->load($productId);
            if($this->_storeId) {
                $product->addStoreFilter($this->_storeId)
                         ->setStoreId($this->_storeId);
            }
            $product->setGoogleShoppingCategory($this->_categoryId)->save();
        }
    }
    
    /**
     * print usage information
     */
    public function usageHelp()
    {
        return <<<USAGE
Usage:  php -f googleshopping_taxonomy_mapping.php -- [options] --productid [int]
 
  action                (s|g)etcategory|syncitems|deleteitems|additems
  productids            Comma separated Ids of products or single product id
  store                 Id of Store (default = all)
  categoryid            Id of GoogleShopping category
  help                  This help
 
USAGE;
    }
}

$shell = new BlueVisionTec_Shell_GoogleShopping();
$shell->run();