<?php declare(strict_types=1);


namespace Niebvelungen\RestBundleDoctrine\Service;

use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Order;
use Niebvelungen\RestBundleDoctrine\Exceptions\InvalidFilterException;

class QueryMapperService
{
    const AVAILABLE_FILTERS = [
        self::EQUALS_FILTER,
        self::NOT_EQUALS_FILTER,
        self::GREATER_THAN_FILTER,
        self::LESS_THAN_FILTER,
    ];

    const EQUALS_FILTER = 'equals';
    const NOT_EQUALS_FILTER = 'not_equals';
    const GREATER_THAN_FILTER = 'gt';
    const LESS_THAN_FILTER = 'lt';

    const FILTER_TYPE_FIELD = 'type';
    const VALUE_FIELD = 'value';
    const FIELD_NAME_FIELD = 'field';
    const SORTING_ORDER = 'order';

    const SORTING_ORDERS = [Order::Ascending->value, Order::Descending->value];

    const ALLOWED_FILTER_DEPTH = 2;

    /**
     * @throws InvalidFilterException
     */
    public function validateFilters(array $filters): void
    {
        foreach ($filters as $filter){
            $this->validateFilter($filter);
        }
    }

    private function validateFilter(array $filter) : void
    {
        if(!isset($filter[self::FILTER_TYPE_FIELD]) || !isset($filter[self::VALUE_FIELD]) || !isset($filter[self::FIELD_NAME_FIELD])){
            throw new InvalidFilterException('Invalid filter. Could not parse json to filters. Missing Name, Field or Value!');
        }

        $field = $filter[self::FIELD_NAME_FIELD];
        $value = $filter[self::VALUE_FIELD];
        $type = $filter[self::FILTER_TYPE_FIELD];

        if(!in_array($type, self::AVAILABLE_FILTERS))
            throw new InvalidFilterException('Invalid filter type: '. $type . ' Available Filter types: '. implode(', ', self::AVAILABLE_FILTERS));

        $depth = explode('.', $field);

        if(count($depth) > self::ALLOWED_FILTER_DEPTH)
            throw new InvalidFilterException('Filter depth too long. Maximum allowed: '. self::ALLOWED_FILTER_DEPTH . ' For Filter:' . $field);
    }

    public function getJoinsForFilter(array $filters) : array
    {
        $joins = [];
        foreach ($filters as $filter){
            $field = $filter[self::FIELD_NAME_FIELD];
            $pathArray = explode('.', $field);

            if(count($pathArray) == self::ALLOWED_FILTER_DEPTH)
                $joins[] = array_shift($pathArray);

            unset($pathArray);
        }

        return $joins;
    }

    public function applyFiltersToCriteria(array $filters, Criteria $criteria): Criteria
    {
        foreach ($filters as $filter){
            $field = $filter[self::FIELD_NAME_FIELD];
            $value = $filter[self::VALUE_FIELD];
            $type = $filter[self::FILTER_TYPE_FIELD];
            switch ($type)
            {
                case self::EQUALS_FILTER:
                    $criteria->andWhere(Criteria::expr()->eq($field, $value));
                    break;
                case self::NOT_EQUALS_FILTER:
                    $criteria->andWhere(Criteria::expr()->neq($field, $value));
                    break;
                case self::GREATER_THAN_FILTER:
                    $criteria->andWhere(Criteria::expr()->gt($field, $value));
                    break;
                case self::LESS_THAN_FILTER:
                    $criteria->andWhere(Criteria::expr()->lt($field, $value));
                    break;
            }
        }

        return $criteria;
    }

    public function validateSorting(array $sorting): void
    {
        foreach ($sorting as $sort){
            if(!isset($sort[self::FIELD_NAME_FIELD]))
                throw new InvalidFilterException('Sorting field is required!');

            if(isset($sort[self::SORTING_ORDER])){
                $order = strtoupper(trim($sort[self::SORTING_ORDER]));

                if(!in_array($order, self::SORTING_ORDERS))
                    throw new InvalidFilterException('Invalid sorting order: '.$order.' Available sorting orders: '. implode("," ,self::SORTING_ORDERS));
            }
        }
    }

    public function applySortingToCriteria(array $sorting, Criteria $criteria): Criteria
    {
        $orderBy = [];

        foreach ($sorting as $sort){
            $field = $sort[self::FIELD_NAME_FIELD];
            $order = isset($sort[self::SORTING_ORDER]) ? strtoupper($sort[self::SORTING_ORDER]) : Order::Ascending->value;

            $orderBy[$field] = $order;

        }

        $criteria->orderBy($orderBy);

        return $criteria;
    }
}