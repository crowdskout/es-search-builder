<?php
namespace Crowdskout\ElasticsearchQueryBuilder\Agg\Generator;

use Crowdskout\ElasticsearchQueryBuilder\Agg\AggResult;

class AggGenerator implements AggGeneratorInterface
{
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
}
