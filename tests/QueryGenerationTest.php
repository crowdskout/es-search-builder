<?php
namespace Crowdskout\EsSearchBuilder\Tests;

use Crowdskout\EsSearchBuilder\Query;

class QueryGenerationTest extends TestCase
{
    public function testQuery()
    {
        $query = Query::filter([
            Query::terms('intField', [1, 2, 3, 4, 5, 6]),
            Query::nest('stringField', Query::term('stringField.keyword', 'a keyword'))
        ]);
        $this->assertEquals([
            'bool' => [
                'filter' => [
                    [
                        'terms' => [
                            'intField' => [1,2,3,4,5,6]
                        ]
                    ],
                    [
                        'nested' => [
                            'path' => 'stringField',
                            'query' => [
                                'term' => [
                                    'stringField.keyword' => 'a keyword'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ], $query);
    }

    public function testWildcardQuery()
    {
        $query = Query::filter([
            Query::terms('intField', [1, 2, 3, 4, 5, 6]),
            Query::nest('stringField', Query::wildcard('stringField.keyword', 'a key*'))
        ]);
        $this->assertEquals([
            'bool' => [
                'filter' => [
                    [
                        'terms' => [
                            'intField' => [1,2,3,4,5,6]
                        ]
                    ],
                    [
                        'nested' => [
                            'path' => 'stringField',
                            'query' => [
                                'wildcard' => [
                                    'stringField.keyword' => 'a key*'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ], $query);
    }
}
