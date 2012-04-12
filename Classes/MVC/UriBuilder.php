<?php

class Tx_Doctrine2_MVC_UriBuilder extends Tx_Extbase_MVC_Web_Routing_UriBuilder
{
	protected function convertDomainObjectsToIdentityArrays(array $arguments)
    {
		foreach ($arguments as $argumentKey => $argumentValue) {
            $arguments[$argumentKey] = $this->convertDomainObjectToIdentityArray($argumentValue);
		}
		return $arguments;
	}

    protected function convertDomainObjectToIdentityArray($argumentValue)
    {
        // if we have a LazyLoadingProxy here, make sure to get the real instance for further processing
        if ($argumentValue instanceof \Doctrine\ORM\Proxy\Proxy && !$argumentValue->__isInitialized()) {
            $argumentValue->__load();
        }

        if (is_array($argumentValue)) {
            return $this->convertDomainObjectsToIdentityArrays($argumentValue);
        }

        if (!($argumentValue instanceof Tx_Extbase_DomainObject_AbstractDomainObject)) {
            return $argumentValue;
        }

        if ($argumentValue->getUid() !== NULL) {
            return $argumentValue->getUid();
        }

        if ($argumentValue instanceof Tx_Extbase_DomainObject_AbstractValueObject) {
            return $this->convertTransientObjectToArray($argumentValue);
        }

        throw new Tx_Extbase_MVC_Exception_InvalidArgumentValue('Could not serialize Domain Object ' . get_class($argumentValue) . '. It is neither an Entity with identity properties set, nor a Value Object.', 1260881688);
    }
}

