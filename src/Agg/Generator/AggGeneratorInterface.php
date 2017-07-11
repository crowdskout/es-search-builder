<?php
namespace Crowdskout\ElasticsearchQueryBuilder\Agg\Generator;

interface AggGeneratorInterface
{
    /**
     * @param string $aggName
     * @param bool $keyAsString
     * @return callable
     */
    public static function bucketGenerator($aggName, $keyAsString = false);

    /**
     * @param string $aggName
     * @return callable
     */
    public static function keyedBucketGenerator($aggName);

    /**
     * @param string $aggName
     * @param string $key
     * @param string $valueField
     * @return callable
     */
    public static function singleValueGenerator($aggName, $key = '', $valueField = 'doc_count');
}
