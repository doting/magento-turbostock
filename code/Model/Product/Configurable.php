<?php

class Doting_TurboStock_Model_Product_Configurable extends Mage_Catalog_Model_Product_Type_Configurable {

    public function isSalable($product = null) {
        $cache = Mage::app()->getCache();
        $cacheKey = 'turbostock_'.$product->getEntityId();
        $status = $cache->load($cacheKey);
        if ($status === false) {
            $status = parent::isSalable($product) ? '1' : '0';
            $cache->save($status, $cacheKey);
            Mage::log('registered '.$cacheKey.' as '.$status, null, 'teste.log');
        }
        return (boolean) $status;
    }

}
