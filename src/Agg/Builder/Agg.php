<?php
namespace Crowdskout\ElasticsearchQueryBuilder\Agg\Builder;

use Crowdskout\ElasticsearchQueryBuilder\Agg\Aggregation;
use Crowdskout\ElasticsearchQueryBuilder\Agg\AggregationInterface;
use Crowdskout\ElasticsearchQueryBuilder\Agg\AggregationMulti;
use Crowdskout\ElasticsearchQueryBuilder\Agg\AggResult;

class Agg
{
    /**
     * @param array $query
     * @param Aggregation $nestedAgg
     * @param string|null $filterKey
     * @return Aggregation
     */
    public static function filter($query, $nestedAgg = null, $filterKey = '')
    {
        $aggName = 'filter_agg';
        $query = [
            $aggName => [
                'filter' => $query
            ]
        ];
        return new Aggregation($query, self::singleValueGenerator($aggName, $filterKey), $nestedAgg);
    }

    /**
     * @param array $queries
     * @param Aggregation $nestedAgg
     * @return Aggregation
     */
    public static function filters($queries, $nestedAgg = null)
    {
        $aggName = 'filters_agg';
        $query = [
            $aggName => [
                'filters' => [
                    'filters' => $queries,
                ]
            ]
        ];
        return new Aggregation($query, self::keyedBucketGenerator($aggName), $nestedAgg);
    }

    /**
     * @param string $field
     * @param array $termsOptions
     * @param Aggregation $nestedAgg
     * @return Aggregation
     */
    public static function terms($field, $termsOptions = [], $nestedAgg = null)
    {
        $aggName = "{$field}_terms_agg";
        $termsOptions['field'] = $field;
        $query = [
            $aggName => [
                'terms' => $termsOptions
            ]
        ];
        return new Aggregation($query, self::bucketGenerator($aggName), $nestedAgg);
    }

    /**
     * @param string $field
     * @param array $dateHistogramOptions
     * @param Aggregation $nestedAgg
     * @return Aggregation
     */
    public static function dateHistogram($field, $dateHistogramOptions = [], $nestedAgg = null)
    {
        $dateHistogramOptions['field'] = $field;
        $aggName = "{$field}_date_histogram_agg";
        $query = [
            $aggName => [
                'date_histogram' => $dateHistogramOptions
            ]
        ];
        return new Aggregation($query, self::bucketGenerator($aggName, true), $nestedAgg);
    }

    /**
     * @param string $field
     * @param array $ranges
     * @param Aggregation $nestedAgg
     * @return Aggregation
     */
    public static function range($field, $ranges, $nestedAgg = null)
    {
        $aggName = "{$field}_range_agg";
        $query = [
            $aggName => [
                'range' => [
                    'field' => $field,
                    'ranges' => $ranges
                ]
            ]
        ];
        return new Aggregation($query, self::bucketGenerator($aggName), $nestedAgg);
    }

    /**
     * @param string $field
     * @param Aggregation $nestedAgg
     * @return Aggregation
     */
    public static function sum($field, $nestedAgg = null)
    {
        $aggName = "{$field}_sum_agg";
        $query = [
            $aggName => [
                'sum' => [
                    'field' => $field
                ]
            ]
        ];
        return new Aggregation($query, self::singleValueGenerator($aggName, "Sum", "value"), $nestedAgg);
    }

    /**
     * @param string $path
     * @param array $nestedAgg
     * @return Aggregation
     */
    public static function nested($path, $nestedAgg = null)
    {
        $aggName = "{$path}_nested_agg";
        $query = [
            $aggName => [
                'nested' => [
                    'path' => $path
                ]
            ]
        ];
        return new Aggregation($query, self::singleValueGenerator($aggName), $nestedAgg);
    }

    /**
     * @param Aggregation $nestedAgg
     * @return Aggregation
     */
    public static function reverseNested($nestedAgg = null)
    {
        $aggName = "reverse_nested_agg";
        $query = [
            $aggName => [
                'reverse_nested' => (object)[]
            ]
        ];
        return new Aggregation($query, self::singleValueGenerator($aggName), $nestedAgg);
    }

    /**
     * @param AggregationInterface[] $aggs
     * @return AggregationMulti
     */
    public static function multi($aggs)
    {
        return new AggregationMulti($aggs);
    }

    /**
     * @param string $aggName
     * @param bool $keyAsString
     * @return callable
     */
    public static function bucketGenerator($aggName, $keyAsString = false)
    {
        // should be more efficient to have the logic outside of the generator and loop
        if ($keyAsString) {
            $generator = function ($results) use ($aggName) {
                foreach ($results[$aggName]['buckets'] as $bucket) {
                    yield $bucket['key_as_string'] => new AggResult($bucket['doc_count'], $bucket);
                }
            };
        } else {
            $generator = function ($results) use ($aggName) {
                foreach ($results[$aggName]['buckets'] as $bucket) {
                    yield ucwords($bucket['key']) => new AggResult($bucket['doc_count'], $bucket);
                }
            };
        }

        return $generator;
    }

    /**
     * @param string $aggName
     * @return callable
     */
    public static function keyedBucketGenerator($aggName)
    {
        $generator = function ($results) use ($aggName) {
            foreach ($results[$aggName]['buckets'] as $key => $bucket) {
                yield ucwords($key) => new AggResult($bucket['doc_count'], $bucket);
            }
        };

        return $generator;
    }

    /**
     * @param string $aggName
     * @param string $key
     * @param string $valueField
     * @return callable
     */
    public static function singleValueGenerator($aggName, $key = '', $valueField = 'doc_count')
    {
        if ($key === '') {
            $generator = function ($results) use ($aggName, $valueField) {
                yield new AggResult($results[$aggName][$valueField], $results[$aggName]);
            };
        } else {
            $generator = function ($results) use ($aggName, $key, $valueField) {
                yield $key => new AggResult($results[$aggName][$valueField], $results[$aggName]);
            };
        }

        return $generator;
    }

    public static function make()
    {
        return new Aggregation([], self::emptyGenerator());
    }

    public static function emptyGenerator()
    {
        return function ($results) {
            yield new AggResult(0, $results);
        };
    }

}
