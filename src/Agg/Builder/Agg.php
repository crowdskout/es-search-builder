<?php
namespace Crowdskout\ElasticsearchQueryBuilder\Agg\Builder;

use Crowdskout\ElasticsearchQueryBuilder\Agg\Aggregation;
use Crowdskout\ElasticsearchQueryBuilder\Agg\AggregationInterface;
use Crowdskout\ElasticsearchQueryBuilder\Agg\AggregationMulti;
use Crowdskout\ElasticsearchQueryBuilder\Agg\AggResult;
use Crowdskout\ElasticsearchQueryBuilder\Agg\Generator\AggGeneratorInterface;
use Crowdskout\ElasticsearchQueryBuilder\Agg\Generator\DefaultAggGenerator;

class Agg
{
    /** @var AggGeneratorInterface */
    protected $aggGenerator;

    public function __construct(AggGeneratorInterface $aggGenerator = null)
    {
        if ($aggGenerator === null) {
            $aggGenerator = new DefaultAggGenerator();
        }
        $this->aggGenerator = $aggGenerator;
    }

    /**
     * @param array $query
     * @param Aggregation $nestedAgg
     * @param string|null $filterKey
     * @return Aggregation
     */
    public function filter($query, $nestedAgg = null, $filterKey = '')
    {
        $aggName = 'filter_agg';
        return new Aggregation(
            AggQuery::filter($query, $aggName),
            $this->aggGenerator->getFilterGenerator($aggName, $filterKey),
            $nestedAgg
        );
    }

    /**
     * @param array $queries
     * @param Aggregation $nestedAgg
     * @return Aggregation
     */
    public function filters($queries, $nestedAgg = null)
    {
        $aggName = 'filters_agg';
        return new Aggregation(
            AggQuery::filters($queries, $aggName),
            $this->aggGenerator->getFiltersGenerator($aggName),
            $nestedAgg
        );
    }

    /**
     * @param string $field
     * @param array $termsOptions
     * @param Aggregation $nestedAgg
     * @return Aggregation
     */
    public function terms($field, $termsOptions = [], $nestedAgg = null)
    {
        $aggName = "{$field}_terms_agg";
        return new Aggregation(
            AggQuery::terms($field, $termsOptions, $aggName),
            $this->aggGenerator->getTermsGenerator($aggName),
            $nestedAgg
        );
    }

    /**
     * @param string $field
     * @param array $dateHistogramOptions
     * @param Aggregation $nestedAgg
     * @param bool $keyAsString
     * @return Aggregation
     */
    public function dateHistogram($field, $dateHistogramOptions = [], $nestedAgg = null, $keyAsString = true)
    {
        $aggName = "{$field}_date_histogram_agg";
        return new Aggregation(
            AggQuery::dateHistogram($field, $dateHistogramOptions, $aggName),
            $this->aggGenerator->getDateHistogramGenerator($aggName, $keyAsString),
            $nestedAgg
        );
    }

    /**
     * @param string $field
     * @param array $ranges
     * @param array $rangeOptions
     * @param Aggregation $nestedAgg
     * @return Aggregation
     */
    public function range($field, $ranges, $rangeOptions = [], $nestedAgg = null)
    {
        $aggName = "{$field}_range_agg";
        return new Aggregation(
            AggQuery::range($field, $ranges, $rangeOptions, $aggName),
            $this->aggGenerator->getRangeGenerator($aggName),
            $nestedAgg
        );
    }

    /**
     * @param string $field
     * @param Aggregation $nestedAgg
     * @param string $filterKey
     * @return Aggregation
     */
    public function sum($field, $nestedAgg = null, $filterKey = 'Sum')
    {
        $aggName = "{$field}_sum_agg";
        return new Aggregation(
            AggQuery::sum($field, $aggName),
            $this->aggGenerator->getSumGenerator($aggName, $filterKey),
            $nestedAgg
        );
    }

    /**
     * @param string $path
     * @param array $nestedAgg
     * @return Aggregation
     */
    public function nested($path, $nestedAgg = null)
    {
        $aggName = "{$path}_nested_agg";
        return new Aggregation(
            AggQuery::nested($path, $aggName),
            $this->aggGenerator->getNestedGenerator($aggName),
            $nestedAgg
        );
    }

    /**
     * @param Aggregation $nestedAgg
     * @return Aggregation
     */
    public function reverseNested($nestedAgg = null)
    {
        $aggName = "reverse_nested_agg";
        return new Aggregation(
            AggQuery::reverseNested($aggName),
            $this->aggGenerator->getReverseNestedGenerator($aggName),
            $nestedAgg
        );
    }

    /**
     * @param AggregationInterface[] $aggs
     * @return AggregationMulti
     */
    public function multi($aggs)
    {
        return new AggregationMulti($aggs);
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
