<?php
namespace Crowdskout\ElasticsearchQueryBuilder\Tests;

use Crowdskout\ElasticsearchQueryBuilder\Builders\Agg;
use Crowdskout\ElasticsearchQueryBuilder\Builders\Query;

class AggregationGenerationTest extends TestCase
{
    public function testFilterAggregation()
    {
        $agg = Agg::filter(Query::term('test_term', 1));
        $this->assertEquals([
            'filter_agg' => [
                'filter' => [
                    'term' => [
                        'test_term' => 1
                    ]
                ]
            ]
        ], $agg->generateQuery());

        $this->assertEquals(5, $agg->generateResults([
            'filter_agg' => [
                'doc_count' => 5
            ]
        ]));
    }

    public function testFiltersAggregation()
    {
        $agg = Agg::filters([
            'TestTerm1' => Query::term('test_term', 1),
            'TestTerm2' => Query::term('test_term', 2)
        ]);

        $this->assertEquals([
            'filters_agg' => [
                'filters' => [
                    'filters' => [
                        'TestTerm1' => [
                            'term' => [
                                'test_term' => 1
                            ]
                        ],
                        'TestTerm2' => [
                            'term' => [
                                'test_term' => 2
                            ]
                        ]
                    ]
                ]
            ]
        ], $agg->generateQuery());

        $this->assertEquals([
            "Total" => 5,
            'options' => [
                "TestTerm1" => 3,
                "TestTerm2" => 2,
            ]
        ], $agg->generateResults([
            'filters_agg' => [
                'doc_count' => 5,
                'buckets' => [
                    'TestTerm1' => [
                        'doc_count' => 3
                    ],
                    'TestTerm2' => [
                        'doc_count' => 2
                    ],
                ]
            ]
        ]));
    }

    public function testDateHistogramAggregation()
    {
        $dateHistogramField = 'when';
        $dateHistogramOptions = [
            'interval' => 'day',
            'offset' => "+0d",
            'time_zone' => "-5",
            'format' => 'date_time_no_millis',
            'min_doc_count' => 0
        ];
        $agg = Agg::dateHistogram($dateHistogramField, $dateHistogramOptions);

        $this->assertEquals([
            'when_date_histogram_agg' => [
                'date_histogram' => [
                    'field' => $dateHistogramField,
                    'interval' => 'day',
                    'offset' => "+0d",
                    'time_zone' => "-5",
                    'format' => 'date_time_no_millis',
                    'min_doc_count' => 0
                ]
            ]
        ], $agg->generateQuery());

        $this->assertEquals([
            "Total" => 5,
            'options' => [
                "2017-01-01T00:05:00+00:00" => 3,
                "2017-01-02T00:05:00+00:00" => 2,
            ]
        ], $agg->generateResults([
            'when_date_histogram_agg' => [
                'doc_count' => 5,
                'buckets' => [
                    [
                        'key' => 1483229100,
                        'key_as_string' => '2017-01-01T00:05:00+00:00',
                        'doc_count' => 3
                    ],
                    [
                        'key' => 1483315500,
                        'key_as_string' => '2017-01-02T00:05:00+00:00',
                        'doc_count' => 2
                    ]
                ]
            ]
        ]));
    }

    public function testRangeAggregation()
    {
        $rangeField = 'integerField';
        $ranges = [
            ['key' => "*-18", 'to' => 18],
            ['key' => "19-70", 'from' => 19, 'to' => 70],
            ['key' => "71-*", 'from' => 70],
        ];
        $agg = Agg::range($rangeField, $ranges);

        $this->assertEquals([
            'integerField_range_agg' => [
                'range' => [
                    'field' => $rangeField,
                    'ranges' => $ranges
                ]
            ]
        ], $agg->generateQuery());

        $this->assertEquals([
            "Total" => 17,
            'options' => [
                "*-18" => 3,
                "19-70" => 10,
                "71-*" => 4,
            ]
        ], $agg->generateResults([
            'integerField_range_agg' => [
                'doc_count' => 17,
                'buckets' => [
                    [
                        'key' => "*-18",
                        'doc_count' => 3
                    ],
                    [
                        'key' => "19-70",
                        'doc_count' => 10
                    ],
                    [
                        'key' => "71-*",
                        'doc_count' => 4
                    ],
                ]
            ]
        ]));
    }

    public function testSumAggregation()
    {
        $sumField = 'someField';
        $agg = Agg::sum($sumField);

        $this->assertEquals([
            "{$sumField}_sum_agg" => [
                'sum' => [
                    'field' => $sumField,
                ]
            ]
        ], $agg->generateQuery());

        $this->assertEquals([
            'Total' => 47,
            'options' => [
                "Sum" => 47
            ]
        ], $agg->generateResults([
            "{$sumField}_sum_agg" => [
                'value' => 47
            ]
        ]));
    }

    public function testReverseNestedAggregation()
    {
        $termsField = 'test.nested.field';
        $agg = Agg::terms($termsField, [], Agg::reverseNested());

        $this->assertEquals([
            'test.nested.field_terms_agg' => [
                'terms' => [
                    'field' => 'test.nested.field'
                ],
                'aggs' => [
                    'reverse_nested_agg' => [
                        'reverse_nested' => (object)[]
                    ]
                ]
            ]
        ], $agg->generateQuery());

        $results = $agg->generateResults([
            'test.nested.field_terms_agg' => [
                'buckets' => [
                    [
                        'key' => 'SomeValue1',
                        'doc_count' => 3,
                        'reverse_nested_agg' => [
                            'doc_count' => 2,
                        ]
                    ],
                    [
                        'key' => 'SomeValue2',
                        'doc_count' => 2,
                        'reverse_nested_agg' => [
                            'doc_count' => 1,
                        ]
                    ],
                    [
                        'key' => 'SomeValue3',
                        'doc_count' => 5,
                        'reverse_nested_agg' => [
                            'doc_count' => 3,
                        ]
                    ]
                ]
            ]
        ]);

        $this->assertEquals([
            'Total' => 6,
            'options' => [
                'SomeValue1' => 2,
                'SomeValue2' => 1,
                'SomeValue3' => 3
            ]
        ], $results);
    }

    public function testNestedAggregation()
    {
        $agg = Agg::nested('parentField', Agg::terms('parentField.value'));
        $this->assertEquals([
            'parentField_nested_agg' => [
                'nested' => [
                    'path' => 'parentField',
                ],
                'aggs' => [
                    'parentField.value_terms_agg' => [
                        'terms' => [
                            'field' => 'parentField.value'
                        ]
                    ]
                ]
            ]
        ], $agg->generateQuery());

        $results = $agg->generateResults([
            'parentField_nested_agg' => [
                'doc_count' => 10,
                'parentField.value_terms_agg' => [
                    'buckets' => [
                        [
                            'key' => 'ParentValue1',
                            'doc_count' => 3
                        ],
                        [
                            'key' => 'ParentValue2',
                            'doc_count' => 2
                        ],
                        [
                            'key' => 'ParentValue3',
                            'doc_count' => 5
                        ]
                    ]
                ]
            ]
        ]);

        $this->assertEquals([
            'Total' => 10,
            'options' => [
                'ParentValue1' => 3,
                'ParentValue2' => 2,
                'ParentValue3' => 5
            ]
        ], $results);
    }

    public function testAggregationMulti()
    {
        $agg = Agg::nested('parentField')->setMulti(Agg::multi([
            "Value1" => Agg::filter(Query::term('parentField.value', "Value1")),
            "Value2" => Agg::filter(Query::term('parentField.value', "Value2")),
        ]));
        $this->assertEquals([
            'parentField_nested_agg' => [
                'nested' => [
                    'path' => 'parentField',
                ],
                'aggs' => [
                    '0_filter_agg' => [
                        'filter' => [
                            'term' => [
                                'parentField.value' => 'Value1'
                            ]
                        ]
                    ],
                    '1_filter_agg' => [
                        'filter' => [
                            'term' => [
                                'parentField.value' => 'Value2'
                            ]
                        ]
                    ]
                ]
            ]
        ], $agg->generateQuery());

        $results = $agg->generateResults([
            'parentField_nested_agg' => [
                'doc_count' => 5,
                '0_filter_agg' => [
                    'doc_count' => 2
                ],
                '1_filter_agg' => [
                    'doc_count' => 3
                ]
            ]
        ]);

        $this->assertEquals([
            'Total' => 5,
            'options' => [
                'Value2' => 3,
                'Value1' => 2,
            ]
        ], $results);


        // order not guaranteed in the results
        $results = $agg->generateResults([
            'parentField_nested_agg' => [
                'doc_count' => 5,
                '1_filter_agg' => [
                    'doc_count' => 3
                ],
                '0_filter_agg' => [
                    'doc_count' => 2
                ]
            ]
        ]);

        $this->assertEquals([
            'Total' => 5,
            'options' => [
                'Value2' => 3,
                'Value1' => 2,
            ]
        ], $results);


        // Testing that the order put into the array is what gets returned
        $agg = Agg::nested('parentField')->setMulti(Agg::multi([
            "Value2" => Agg::filter(Query::term('parentField.value', "Value2")),
            "Value1" => Agg::filter(Query::term('parentField.value', "Value1"))
        ]));

        $this->assertEquals([
            'parentField_nested_agg' => [
                'nested' => [
                    'path' => 'parentField',
                ],
                'aggs' => [
                    '0_filter_agg' => [
                        'filter' => [
                            'term' => [
                                'parentField.value' => 'Value2'
                            ]
                        ]
                    ],
                    '1_filter_agg' => [
                        'filter' => [
                            'term' => [
                                'parentField.value' => 'Value1'
                            ]
                        ]
                    ]
                ]
            ]
        ], $agg->generateQuery());

        $results = $agg->generateResults([
            'parentField_nested_agg' => [
                'doc_count' => 5,
                '1_filter_agg' => [
                    'doc_count' => 2
                ],
                '0_filter_agg' => [
                    'doc_count' => 3
                ]
            ]
        ]);

        $this->assertEquals([
            'Total' => 5,
            'options' => [
                'Value1' => 2,
                'Value2' => 3,
            ]
        ], $results);

        $results = $agg->generateResults([
            'parentField_nested_agg' => [
                'doc_count' => 5,
                '0_filter_agg' => [
                    'doc_count' => 3
                ],
                '1_filter_agg' => [
                    'doc_count' => 2
                ]
            ]
        ]);

        $this->assertEquals([
            'Total' => 5,
            'options' => [
                'Value1' => 2,
                'Value2' => 3,
            ]
        ], $results);
    }
}
