<?php
class Tx_Doctrine2_Property_Mapper extends Tx_Extbase_Property_Mapper
{
    public function injectQueryFactory(Tx_Doctrine2_QueryFactory $queryFactory)
    {
        $this->queryFactory = $queryFactory;
    }
}
