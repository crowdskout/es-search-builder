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

