<?php

class Tx_Doctrine2_Utility_Cache
{
    public function clearProxyCache($params)
    {
return;
        if (in_array($params['cacheCmd'], array('all', 'temp_CACHED'))) {
            $objectManager = t3lib_div::makeInstance('Tx_Extbase_Object_ObjectManager');
            $manager = $objectManager->get('Tx_Extbase_Persistence_ManagerInterface');
            $em = $manager->getEntityManager();
            
            $em->getProxyFactory()->generateProxyClasses($em->getMetadataFactory()->getAllMetadata());
        }
    }
}

?>
