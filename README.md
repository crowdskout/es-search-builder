# es-search-builder
This package provides simplified api and results parsing for Elasticsearch search queries and aggregations.  It builds upon the official elasticsearch-php library here: https://github.com/elastic/elasticsearch-php.

## Quick example - Query api
```php
    $query = Query::terms('intField', [1, 2, 3, 4, 5, 6])
    print_r($query);
```
The query api provides simpler, less verbose query generation.
```php
    Array
    (
        [terms] => Array
            (
                [intField] => Array
                    (
                        [0] => 1
                        [1] => 2
                        [2] => 3
                        [3] => 4
                        [4] => 5
                        [5] => 6
                    )
    
            )
    
    )
```

## Quick example - Agg builder api
```php
    use Crowdskout\ElasticsearchQueryBuilder\Agg\Builder\Agg as AggBuilder;
    
    $aggBuilder = new AggBuilder();
    
    $agg = $aggBuilder->nested('parentField', $aggBuilder->terms('parentField.subField'));
    $aggQuery = $agg->generateQuery();

    print_r($aggQuery);
```
Aggregations can be nested with each other
```
Array
(
    [parentField_nested_agg] => Array
        (
            [nested] => Array
                (
                    [path] => parentField
                )

            [aggs] => Array
                (
                    [parentField.subField_terms_agg] => Array
                        (
                            [terms] => Array
                                (
                                    [field] => parentField.subField
                                )

                        )

                )

        )

)
```
Use the agg object to parse the results from Elasticsearch as well
```php
    // ... (code from above)
    
    // Aggregation results from Elasticsearch
    $elasticResult = [
         'parentField_nested_agg' => [
             'doc_count' => 5,
             'parentField.subField_terms_agg' => [
                 'buckets' => [
                     [
                         'key' => 'subFieldValue1',
                         'doc_count' => 3
                     ],
                     [
                         'key' => 'subFieldValue2',
                         'doc_count' => 2
                     ]
                 ]
             ]
         ]
     ];

    $parsedResult = $agg->generateResults($elasticResult);
    print_r($parsedResult);
```
```php
    Array
    (
        [Total] => 5
        [options] => Array
            (
                [subFieldValue1] => 3
                [subFieldValue2] => 2
            )
    
    )
```

# Installation using Composer
If you don't have composer, please install it - https://getcomposer.org/

Add this package to your project from the terminal.
```bash
composer require crowdskout/es-search-builder
```

If your project is not already setup to autoload composer libraries, you can put this at the top of your boostrap file or script
```php
    use Crowdskout\ElasticsearchQueryBuilder\Agg\Builder\Agg as AggBuilder;
    use Crowdskout\ElasticsearchQueryBuilder\Query\Builder\Query;

    require 'vendor/autoload.php';

    // Query
    $query = Query::terms('intField', [1, 2, 3, 4, 5, 6])
    
    // Agg
    $aggBuilder = new AggBuilder();
        
    $agg = $aggBuilder->nested('parentField', $aggBuilder->terms('parentField.subField'));
    $aggQuery = $agg->generateQuery();
```

# Usage with elasticsearch-php
This library creates simple arrays to pass into the body portion of Elasticsearch search queries.  You can pass the aggregation portion of the search result into the generateQuery function of the aggregation.
```php
    use Crowdskout\ElasticsearchQueryBuilder\Agg\Builder\Agg as AggBuilder;
    use Crowdskout\ElasticsearchQueryBuilder\Query\Builder\Query;
    use Elasticsearch\ClientBuilder;
    
    // Build a query
    $query = Query::terms('intField', [1, 2, 3, 4, 5, 6])
    
    // Build an aggregation
    $aggBuilder = new AggBuilder();
    $agg = $aggBuilder->nested('parentField', $aggBuilder->terms('parentField.subField'));
    
    // Initialize the Elasticsearch client
    $client = ClientBuilder::create()->build()
    
    // Run the search query
    $params = [
            'index' => 'my_index',
            'type' => 'my_type',
            'body' => [
                'query' => $query,
                'aggs' => $agg->generateQuery()
            ]
        ];
    $response = $client->search($params);
    
    // Parse the results
    $parsedResult = $agg->generateResults($response['aggregations']);
```

# This library does not currently support all queries and aggregations
The current supported queries are here: https://github.com/crowdskout/es-search-builder/blob/master/src/Query/Builder/Query.php.
The current supported aggregations are here: https://github.com/crowdskout/es-search-builder/blob/master/src/Agg/Builder/AggQuery.php.

If there's a query or or aggregation that you would like to see supported, please open an issue.  You can also take as stab at writing it and open a pull request :).


# Additional examples
You can see other, more complex examples of queries and aggregations in the tests/ directory.  You can also see an example of a custom agg results generator in tests/AggGeneratorTest.php/