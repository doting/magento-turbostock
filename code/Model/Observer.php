<?php

class Doting_TurboStock_Model_Observer {

    public function isEnabled() {
        return false;
    }

    public function catalogInventorySave(Varien_Event_Observer $observer) {
        $cache = Mage::app()->getCache();
        //Mage::log('catalogInventorySave', null, 'teste.log');
        $event = $observer->getEvent();
        $stockItem = $event->getItem();
        //Mage::log($stockItem->getProductId(), null, 'teste.log');
        $product = Mage::getModel('catalog/product')->load($stockItem->getProductId());
        //$product->setData('is_salable', '1');
        //$product->getTypeInstance()->setProduct($product);

        $parents = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($product->getId());
        foreach ($parents as $parentId) {
            $parent = Mage::getModel('catalog/product')->load($parentId);
            $cacheKey = 'turbostock_'.$parent->getEntityId();
            //Mage::log('parent '.$parent->getName(), null, 'teste.log');
            //Mage::log('cleaned '.$cacheKey, null, 'teste.log');
            $cache->remove($cacheKey);
            //$parent->setData('is_salable', '1');
            //$parent->isSalable();
        }
        //Mage::log(json_encode($parents), null, 'teste.log');
        /*if ($this->isEnabled()) {
            $event = $observer->getEvent();
            $_item = $event->getItem();
            Mage::log(get_class($_item), null, 'teste.log');
            if ((int)$_item->getData('qty') != (int)$_item->getOrigData('qty')) {
                $params = array();
                $params['product_id'] = $_item->getProductId();
                $params['qty'] = $_item->getQty();
                $params['qty_change'] = $_item->getQty() - $_item->getOrigData('qty');
            }
        }*/
    }

    public function subtractQuoteInventory(Varien_Event_Observer $observer) {
        Mage::log('teste sales_model_service_quote_submit_success subtractQuoteInventory', null, 'teste.log');
        $quote = $observer->getEvent()->getQuote();
        $cache = Mage::app()->getCache();
        foreach ($quote->getAllItems() as $item) {
            $product = $item->getProduct();
            $cacheKey = 'turbostock_'.$product->getEntityId();
            Mage::log('cleaned '.$cacheKey, null, 'teste.log');
            $cache->remove($cacheKey);
            //$product->isSalable();
        }
        /*if ($this->isEnabled()) {
            $quote = $observer->getEvent()->getQuote();
            foreach ($quote->getAllItems() as $item) {
                $params = array();
                $params['product_id'] = $item->getProductId();
                $params['sku'] = $item->getSku();
                $params['qty'] = $item->getProduct()->getStockItem()->getQty();
                $params['qty_change'] = ($item->getTotalQty() * -1);
            }
        }*/
    }

    public function revertQuoteInventory(Varien_Event_Observer $observer) {
        if ($this->isEnabled()) {
            $quote = $observer->getEvent()->getQuote();
            foreach ($quote->getAllItems() as $item) {
                $params = array();
                $params['product_id'] = $item->getProductId();
                $params['sku'] = $item->getSku();
                $params['qty'] = $item->getProduct()->getStockItem()->getQty();
                $params['qty_change'] = ($item->getTotalQty());
            }
        }
    }

    public function cancelOrderItem(Varien_Event_Observer $observer) {
        if ($this->isEnabled()) {
            $item = $observer->getEvent()->getItem();
            $qty = $item->getQtyOrdered() - max($item->getQtyShipped(), $item->getQtyInvoiced()) - $item->getQtyCanceled();
            $params = array();
            $params['product_id'] = $item->getProductId();
            $params['sku'] = $item->getSku();
            $params['qty'] = $item->getProduct()->getStockItem()->getQty();
            $params['qty_change'] = $qty;
        }
    }

    public function refundOrderInventory(Varien_Event_Observer $observer) {
        if ($this->isEnabled()) {
            $creditmemo = $observer->getEvent()->getCreditmemo();
            foreach ($creditmemo->getAllItems() as $item) {
                $params = array();
                $params['product_id'] = $item->getProductId();
                $params['sku'] = $item->getSku();
                $params['qty'] = $item->getProduct()->getStockItem()->getQty();
                $params['qty_change'] = ($item->getQty());
           }
        }
    }

}
