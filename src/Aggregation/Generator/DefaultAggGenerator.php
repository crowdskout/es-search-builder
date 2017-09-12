<?php
namespace Crowdskout\EsSearchBuilder\Aggregation\Generator;

use Crowdskout\EsSearchBuilder\Aggregation\AggResult;

class DefaultAggGenerator implements AggGeneratorInterface
{
    protected $generators = [];

    public function __construct($generators = [])
    {
        if (!isset($this->generators['singleValueGenerator'])) {
            $this->generators['singleValueGenerator'] = function ($aggName, $key = '', $valueField = 'doc_count') {
                return self::singleValueGenerator($aggName, $key, $valueField);
            };
        }

        if (!isset($this->generators['keyedBucketGenerator'])) {
            $this->generators['keyedBucketGenerator'] = function ($aggName) {
                return self::keyedBucketGenerator($aggName);
            };
        }

        if (!isset($this->generators['bucketGenerator'])) {
            $this->generators['bucketGenerator'] = function ($aggName, $keyAsString = false) {
                return self::bucketGenerator($aggName, $keyAsString);
            };
        }
    }

    public function getFilterGenerator($aggName, $filterKey = '')
    {
        $generator = isset($this->generators['filter'])
            ? $this->generators['filter']
            : $this->generators['singleValueGenerator'];

        return $generator($aggName, $filterKey);
    }

    public function setFilterGenerator($generator)
    {
        $this->generators['filter'] = $generator;
    }

    public function getFiltersGenerator($aggName)
    {
        $generator = isset($this->generators['filters'])
            ? $this->generators['filters']
            : $this->generators['keyedBucketGenerator'];

        return $generator($aggName);
    }

    public function setFiltersGenerator($generator)
    {
        $this->generators['filters'] = $generator;
    }

    public function getTermsGenerator($aggName)
    {
        $generator = isset($this->generators['terms'])
            ? $this->generators['terms']
            : $this->generators['bucketGenerator'];

        return $generator($aggName);
    }

    public function setTermsGenerator($generator)
    {
        $this->generators['terms'] = $generator;
    }

    public function getDateHistogramGenerator($aggName, $keyAsString = true)
    {
        $generator = isset($this->generators['dateHistogram'])
            ? $this->generators['dateHistogram']
            : $this->generators['bucketGenerator'];

        return $generator($aggName, $keyAsString);
    }

    public function setDateHistogramGenerator($generator)
    {
        $this->generators['dateHistogram'] = $generator;
    }

    public function getRangeGenerator($aggName)
    {
        $generator = isset($this->generators['range'])
            ? $this->generators['range']
            : $this->generators['bucketGenerator'];

        return $generator($aggName);
    }

    public function setRangeGenerator($generator)
    {
        $this->generators['range'] = $generator;
    }

    public function getSumGenerator($aggName, $filterKey = 'Sum')
    {
        $generator = isset($this->generators['sum'])
            ? $this->generators['sum']
            : $this->generators['singleValueGenerator'];

        return $generator($aggName, $filterKey, 'value');
    }

    public function setSumGenerator($generator)
    {
        $this->generators['sum'] = $generator;
    }

    public function getNestedGenerator($aggName)
    {
        $generator = isset($this->generators['nested'])
            ? $this->generators['nested']
            : $this->generators['singleValueGenerator'];

        return $generator($aggName);
    }

    public function setNestedGenerator($generator)
    {
        $this->generators['nested'] = $generator;
    }

    public function getReverseNestedGenerator($aggName)
    {
        $generator = isset($this->generators['reverseNested'])
            ? $this->generators['reverseNested']
            : $this->generators['singleValueGenerator'];

        return $generator($aggName);
    }

    public function setReverseNestedGenerator($generator)
    {
        $this->generators['reverseNested'] = $generator;
    }

    public function setSingleValueGenerator($generator)
    {
        $this->generators['singleValueGenerator'] = $generator;
    }

    public function setBucketGenerator($generator)
    {
        $this->generators['bucketGenerator'] = $generator;
    }

    public function setKeyedBucketGenerator($generator)
    {
        $this->generators['keyedBucketGenerator'] = $generator;
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
                    yield $bucket['key'] => new AggResult($bucket['doc_count'], $bucket);
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
                yield $key => new AggResult($bucket['doc_count'], $bucket);
            }
        };

        return $generator;
    }
}
