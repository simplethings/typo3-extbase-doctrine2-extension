<?php

class Tx_Doctrine2_Utility_Cache
{
    public function clearProxyCache($params)
    {
        if (in_array($params['cacheCmd'], array('all', 'temp_CACHED'))) {
            $objectManager = t3lib_div::makeInstance('Tx_Extbase_Object_ObjectManager');
            $manager = $objectManager->get('Tx_Doctrine2_Manager');
            $em = $manager->getEntityManager();
            
            $em->getProxyFactory()->generateProxyClasses($em->getMetadataFactory()->getAllMetadata());
        }
    }
}

?>