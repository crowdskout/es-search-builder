<?php
namespace Crowdskout\ElasticsearchQueryBuilder\Tests;

use Crowdskout\ElasticsearchQueryBuilder\Agg\AggResult;
use Crowdskout\ElasticsearchQueryBuilder\Agg\Builder\Agg as AggBuilder;
use Crowdskout\ElasticsearchQueryBuilder\Agg\Generator\DefaultAggGenerator;

class AggGeneratorTest extends TestCase
{
    public function testCustomAggGenerator()
    {
        // No Custom Agg Generator
        $aggBuilder = new AggBuilder();

        $agg = $aggBuilder->terms('terms_field');
        $this->assertEquals([
            'terms_field_terms_agg' => [
                'terms' => [
                    'field' => 'terms_field'
                ]
            ]
        ], $agg->generateQuery());

        $this->assertEquals([
            'Total' => 9,
            'options' => [
                'term1' => 3,
                'term2' => 4,
                'term3' => 2
            ]
        ], $agg->generateResults([
            'terms_field_terms_agg' => [
                'buckets' => [
                    [
                        'key' => 'term1',
                        'doc_count' => 3,
                    ],
                    [
                        'key' => 'term2',
                        'doc_count' => 4,
                    ],
                    [
                        'key' => 'term3',
                        'doc_count' => 2,
                    ]
                ]
            ]
        ]));

        // With Custom Agg Generator
        $aggGenerator = new DefaultAggGenerator();
        $aggGenerator->setTermsGenerator(function ($aggName) {
            return function ($results) use ($aggName) {
                foreach ($results[$aggName]['buckets'] as $bucket) {
                    yield ucwords($bucket['key']) => new AggResult($bucket['doc_count'], $bucket);
                }
            };
        });

        $aggBuilder = new AggBuilder($aggGenerator);

        $agg = $aggBuilder->terms('terms_field');
        $this->assertEquals([
            'terms_field_terms_agg' => [
                'terms' => [
                    'field' => 'terms_field'
                ]
            ]
        ], $agg->generateQuery());

        $this->assertEquals([
            'Total' => 9,
            'options' => [
                'Term1' => 3,
                'Term2' => 4,
                'Term3' => 2
            ]
        ], $agg->generateResults([
            'terms_field_terms_agg' => [
                'buckets' => [
                    [
                        'key' => 'term1',
                        'doc_count' => 3,
                    ],
                    [
                        'key' => 'term2',
                        'doc_count' => 4,
                    ],
                    [
                        'key' => 'term3',
                        'doc_count' => 2,
                    ]
                ]
            ]
        ]));
    }
}
