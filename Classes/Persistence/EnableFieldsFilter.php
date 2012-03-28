<?php

use Doctrine\ORM\Mapping\ClassMetadata,
    Doctrine\ORM\Query\Filter\SQLFilter;

/**
 * Doctrine Filter that is usable with "enable fields" feature.
 *
 * @see tslib_cObj::enableFields
 *
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 */
class Tx_Doctrine2_Persistence_EnableFieldsFilter extends SQLFilter
{
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias)
    {
        $tableName = $targetEntity->table['name'];

        if ( ! isset($GLOBALS{'TSFE'])) {
            return '';
        }

        $GLOBALS['TSFE']->sys_page->enableFields($tableName, false);
        $enableFields = str_replace($tableName . ".", $targetTableAlias . ".", $enableFields);

        return $enableFields;
    }
}

