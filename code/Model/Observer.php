<?php

class Doting_TurboStock_Model_Observer {

    public function isEnabled() {
        return false;
    }

    public function catalogInventorySave(Varien_Event_Observer $observer) {
        $cache = Mage::app()->getCache();
        $event = $observer->getEvent();
        $stockItem = $event->getItem();
        $product = Mage::getModel('catalog/product')->load($stockItem->getProductId());

        $parents = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($product->getId());
        foreach ($parents as $parentId) {
            $parent = Mage::getModel('catalog/product')->load($parentId);
            $cacheKey = 'turbostock_'.$parent->getEntityId();
            $cache->remove($cacheKey);
        }
    }

    public function subtractQuoteInventory(Varien_Event_Observer $observer) {
        $quote = $observer->getEvent()->getQuote();
        $cache = Mage::app()->getCache();
        foreach ($quote->getAllItems() as $item) {
            $product = $item->getProduct();
            $cacheKey = 'turbostock_'.$product->getEntityId();
            $cache->remove($cacheKey);
        }
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
