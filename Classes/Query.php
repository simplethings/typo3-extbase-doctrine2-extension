<?php

/**
 * Extbase Query using Doctrine2 backend.
 *
 * The code is mostly copied from the FLOW3 Query object and adjusted where
 * necessary.
 */
class Tx_Doctrine2_Query implements Tx_Extbase_Persistence_QueryInterface
{
    /**
     * @var string
     */
    protected $entityClassName;

    /**
     * @var \Doctrine\ORM\QueryBuilder
     */
    protected $queryBuilder;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    /**
     * @var mixed
     */
    protected $constraint;

    /**
     * @var array
     */
    protected $orderings;

    /**
     * @var Tx_Extbase_Persistence_QuerySettingsInterface
     */
    protected $querySettings;

    /**
     * @var integer
     */
    protected $limit;

    /**
     * @var integer
     */
    protected $offset;

    /**
     * @var integer
     */
    protected $parameterIndex = 1;

    /**
     * @var array
     */
    protected $parameters;

    /**
     * @var array
     */
    protected $joins;

    public function __construct($entityClassName)
    {
        $this->entityClassName = $entityClassName;
    }

    public function getResult()
    {
        return $this->queryBuilder->getQuery()->getResult();
    }

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $entityManager
     * @return void
     */
    public function injectEntityManager(\Doctrine\Common\Persistence\ObjectManager $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->queryBuilder = $entityManager->createQueryBuilder()->select('e')->from($this->entityClassName, 'e');
    }

    /**
     * Sets the Query Settings. These Query settings must match the settings expected by
     * the specific Storage Backend.
     *
     * @param Tx_Extbase_Persistence_QuerySettingsInterface $querySettings The Query Settings
     * @return void
     * @api This method is not part of FLOW3 API
     */
    public function setQuerySettings(Tx_Extbase_Persistence_QuerySettingsInterface $querySettings)
    {
        $this->querySettings = $querySettings;
    }

    /**
     * Returns the Query Settings.
     *
     * @return Tx_Extbase_Persistence_QuerySettingsInterface $querySettings The Query Settings
     * @api This method is not part of FLOW3 API
     */
    public function getQuerySettings()
    {
        if (!($this->querySettings instanceof Tx_Extbase_Persistence_QuerySettingsInterface)) {
            throw new Tx_Extbase_Persistence_Exception('Tried to get the query settings without seting them before.', 1248689115);
        }
        return $this->querySettings;
    }

    /**
     * Returns the type this query cares for.
     *
     * @return string
     * @api
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Executes the query against the backend and returns the result
     *
     * @return Tx_Extbase_Persistence_QueryResultInterface|array The query result object or an array if $this->getQuerySettings()->getReturnRawQueryResult() is TRUE
     * @api
     */
    public function execute()
    {
        if ($this->querySettings && $this->getQuerySettings()->getReturnRawQueryResult()) {
            return $this->queryBuilder->getQuery()->getResult();
        }
        return new Tx_Doctrine2_QueryResult($this);
    }

    /**
     * Executes the query against the database and returns the number of matching objects
     *
     * @return integer The number of matching objects
     * @deprecated since Extbase 1.3.0; was removed in FLOW3; will be removed in Extbase 1.5.0
     */
    public function count()
    {
        $originalQuery = $this->queryBuilder->getQuery();
        $dqlQuery = clone $originalQuery;
        $dqlQuery->setParameters($originalQuery->getParameters());
        $dqlQuery->setHint(\Doctrine\ORM\Query::HINT_CUSTOM_TREE_WALKERS, array('Doctrine\ORM\Tools\Pagination\CountWalker'));
        return (int) $dqlQuery->getSingleScalarResult();
    }

    /**
     * Sets the property names to order the result by. Expected like this:
     * array(
     *  'foo' => Tx_Extbase_Persistence_QueryInterface::ORDER_ASCENDING,
     *  'bar' => Tx_Extbase_Persistence_QueryInterface::ORDER_DESCENDING
     * )
     *
     * @param array $orderings The property names to order by
     * @return Tx_Extbase_Persistence_QueryInterface
     * @api
     */
    public function setOrderings(array $orderings)
    {
        $this->orderings = $orderings;
        $this->queryBuilder->resetDQLPart('orderBy');
        foreach ($this->orderings AS $propertyName => $order) {
            $this->queryBuilder->addOrderBy($this->getPropertyNameWithAlias($propertyName), $order);
        }
        return $this;
    }

    public function getOrderings()
    {
        return $this->orderings;
    }

    /**
     * Sets the maximum size of the result set to limit. Returns $this to allow
     * for chaining (fluid interface)
     *
     * @param integer $limit
     * @return Tx_Extbase_Persistence_QueryInterface
     * @api
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
        $this->queryBuilder->setMaxResults($limit);
        return $this;
    }

    /**
     * Sets the start offset of the result set to offset. Returns $this to
     * allow for chaining (fluid interface)
     *
     * @param integer $offset
     * @return Tx_Extbase_Persistence_QueryInterface
     * @api
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;
        $this->queryBuilder->setFirstResult($offset);
        return $this;
    }

    /**
     * The constraint used to limit the result set. Returns $this to allow
     * for chaining (fluid interface)
     *
     * @param object $constraint Some constraint, depending on the backend
     * @return Tx_Extbase_Persistence_QueryInterface
     * @api
     */
    public function matching($constraint)
    {
        $this->constraint = $constraint;
        $this->queryBuilder->where($constraint);
        return $this;
    }

    /**
     * Performs a logical conjunction of the two given constraints.
     *
     * @param mixed $constraint1 The first of multiple constraints or an array of constraints.
     * @return object
     * @api
     */
    public function logicalAnd($constraint1)
    {
        if (is_array($constraint1)) {
            $constraints = $constraint1;
        } else {
            $constraints = func_get_args();
        }
        return call_user_func_array(array($this->queryBuilder->expr(), 'andX'), $constraints);
    }

    /**
     * Performs a logical disjunction of the two given constraints
     *
     * @param mixed $constraint1 The first of multiple constraints or an array of constraints.
     * @return object
     * @api
     */
    public function logicalOr($constraint1)
    {
        if (is_array($constraint1)) {
            $constraints = $constraint1;
        } else {
            $constraints = func_get_args();
        }
        return call_user_func_array(array($this->queryBuilder->expr(), 'orX'), $constraints);
    }

    /**
     * Performs a logical negation of the given constraint
     *
     * @param object $constraint Constraint to negate
     * @return object
     * @api
     */
    public function logicalNot($constraint)
    {
        return $this->queryBuilder->expr()->not($constraint);
    }

    /**
     * Returns an equals criterion used for matching objects against a query
     *
     * @param string $propertyName The name of the property to compare against
     * @param mixed $operand The value to compare with
     * @param boolean $caseSensitive Whether the equality test should be done case-sensitive
     * @return object
     * @api
     */
    public function equals($propertyName, $operand, $caseSensitive = TRUE)
    {
        if ($operand === NULL) {
            return $this->getPropertyNameWithAlias($propertyName) . ' IS NULL';
        } else {
            return $this->queryBuilder->expr()->eq($this->getPropertyNameWithAlias($propertyName), $this->getParamNeedle($operand));
        }
    }

    /**
     * Returns a like criterion used for matching objects against a query
     *
     * @param string $propertyName The name of the property to compare against
     * @param mixed $operand The value to compare with
     * @return object
     * @api
     */
    public function like($propertyName, $operand)
    {
        return $this->queryBuilder->expr()->like($this->getPropertyNameWithAlias($propertyName), $this->getParamNeedle($operand));
    }

    /**
     * Returns a "contains" criterion used for matching objects against a query.
     * It matches if the multivalued property contains the given operand.
     *
     * @param string $propertyName The name of the (multivalued) property to compare against
     * @param mixed $operand The value to compare with
     * @return object
     * @api
     */
    public function contains($propertyName, $operand)
    {
        return '(' . $this->getParamNeedle($operand) . ' MEMBER OF ' . $this->getPropertyNameWithAlias($propertyName) . ')';
    }

    /**
     * Returns an "in" criterion used for matching objects against a query. It
     * matches if the property's value is contained in the multivalued operand.
     *
     * @param string $propertyName The name of the property to compare against
     * @param mixed $operand The value to compare with, multivalued
     * @return object
     * @api
     */
    public function in($propertyName, $operand)
    {
        return $this->queryBuilder->expr()->in($this->getPropertyNameWithAlias($propertyName), $operand);
    }

    /**
     * Returns a less than criterion used for matching objects against a query
     *
     * @param string $propertyName The name of the property to compare against
     * @param mixed $operand The value to compare with
     * @return object
     * @api
     */
    public function lessThan($propertyName, $operand)
    {
        return $this->queryBuilder->expr()->lt($this->getPropertyNameWithAlias($propertyName), $this->getParamNeedle($operand));
    }

    /**
     * Returns a less or equal than criterion used for matching objects against a query
     *
     * @param string $propertyName The name of the property to compare against
     * @param mixed $operand The value to compare with
     * @return object
     * @api
     */
    public function lessThanOrEqual($propertyName, $operand)
    {
        return $this->queryBuilder->expr()->lte($this->getPropertyNameWithAlias($propertyName), $this->getParamNeedle($operand));
    }

    /**
     * Returns a greater than criterion used for matching objects against a query
     *
     * @param string $propertyName The name of the property to compare against
     * @param mixed $operand The value to compare with
     * @return object
     * @api
     */
    public function greaterThan($propertyName, $operand)
    {
        return $this->queryBuilder->expr()->gt($this->getPropertyNameWithAlias($propertyName), $this->getParamNeedle($operand));
    }

    /**
     * Returns a greater than or equal criterion used for matching objects against a query
     *
     * @param string $propertyName The name of the property to compare against
     * @param mixed $operand The value to compare with
     * @return object
     * @api
     */
    public function greaterThanOrEqual($propertyName, $operand)
    {
        return $this->queryBuilder->expr()->gte($this->getPropertyNameWithAlias($propertyName), $this->getParamNeedle($operand));
    }

    /**
     * Get a needle for parameter binding.
     *
     * @param mixed $operand
     * @return string
     */
    protected function getParamNeedle($operand)
    {
        $index = $this->parameterIndex++;
        $this->queryBuilder->setParameter($index, $operand);
        return '?' . $index;
    }

    /**
     * Adds left join clauses along the given property path to the query, if needed.
     * This enables us to set conditions on related objects.
     *
     * @param string $propertyPath The path to a sub property, e.g. property.subProperty.foo, or a simple property name
     * @return string The last part of the property name prefixed by the used join alias, if joins have been added
     */
    protected function getPropertyNameWithAlias($propertyPath)
    {
        if (strpos($propertyPath, '.') === FALSE) {
            return $this->queryBuilder->getRootAlias() . '.' . $propertyPath;
        }

        $propertyPathParts = explode('.', $propertyPath);
        $conditionPartsCount = count($propertyPathParts);
        $previousJoinAlias = $this->queryBuilder->getRootAlias();
        for ($i = 0; $i < $conditionPartsCount - 1; $i++) {
            $joinAlias = uniqid($propertyPathParts[$i]);
            $this->queryBuilder->leftJoin($previousJoinAlias . '.' . $propertyPathParts[$i], $joinAlias);
            $this->joins[$joinAlias] = $previousJoinAlias . '.' . $propertyPathParts[$i];
            $previousJoinAlias = $joinAlias;
        }

        return $previousJoinAlias . '.' . $propertyPathParts[$i];
    }

    /**
     * We need to drop the query builder, as it contains a PDO instance deep inside.
     *
     * @return array
     */
    public function __sleep()
    {
        $this->parameters = $this->queryBuilder->getParameters();
        return array('entityClassName', 'constraint', 'orderings', 'parameterIndex', 'limit', 'offset', 'parameters', 'joins');
    }

    /**
     * Recreate query builder and set state again.
     *
     * @return void
     */
    public function __wakeup()
    {
        if ($this->constraint !== NULL) {
            $this->queryBuilder->where($this->constraint);
        }

        if (is_array($this->orderings)) {
            foreach ($this->orderings AS $propertyName => $order) {
                $this->queryBuilder->addOrderBy($this->queryBuilder->getRootAlias() . '.' . $propertyName, $order);
            }
        }
        if (is_array($this->joins)) {
            foreach ($this->joins as $joinAlias => $join) {
                $this->queryBuilder->leftJoin($join, $joinAlias);
            }
        }
        $this->queryBuilder->setFirstResult($this->offset);
        $this->queryBuilder->setMaxResults($this->limit);
        $this->queryBuilder->setParameters($this->parameters);
        unset($this->parameters);
    }

    /**
     * Cloning the query clones also the internal QueryBuilder,
     * as they are tightly coupled.
     */
    public function __clone()
    {
        $this->queryBuilder = clone $this->queryBuilder;
    }

}

