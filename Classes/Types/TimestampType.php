<?php

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * New datatype for integer based timestamps.
 *
 * @author Benjamin Eberlei <eberlei@simplethings.de>
 */
class Tx_Doctrine2_Types_TimestampType extends Type
{
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return $platform->getIntegerTypeDeclarationSQL($fieldDeclaration);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return ($value instanceof \DateTime)
            ? $value->format('U') : 0;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value === null || $value == 0) {
            return null;
        }

        $val = new \DateTime('@' . $value);
        if (!$val) {
            throw ConversionException::conversionFailedFormat($value, $this->getName(), $platform->getDateTimeFormatString());
        }
        return $val;
    }

    public function getName()
    {
        return 'timestamp';
    }
}

