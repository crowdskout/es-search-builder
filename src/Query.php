<?php
namespace Crowdskout\EsSearchBuilder;

class Query
{
    /**
     * @param array $filterQueries
     * @param array $mustQueries
     * @param array $shouldQueries
     * @param array $mustNotQueries
     * @return array
     */
    public static function bool($filterQueries = [], $mustQueries = [], $shouldQueries = [], $mustNotQueries = [])
    {
        $out = [];
        if (!empty($filterQueries)) {
            $out['bool']['filter'] = $filterQueries;
        }
        if (!empty($mustQueries)) {
            $out['bool']['must'] = $mustQueries;
        }
        if (!empty($shouldQueries)) {
            $out['bool']['should'] = $shouldQueries;
        }
        if (!empty($mustNotQueries)) {
            $out['bool']['must_not'] = $mustNotQueries;
        }
        return $out;
    }

    public static function filter($filterQueries)
    {
        return self::bool($filterQueries);
    }

    public static function must($mustQueries)
    {
        return self::bool([], $mustQueries);
    }

    public static function should($shouldQueries)
    {
        return self::bool([], [], $shouldQueries);
    }

    public static function mustNot($mustNotQueries)
    {
        return self::bool([], [], [], $mustNotQueries);
    }

    /**
     * @param string $field
     * @param string[]|int[] $terms
     * @return array
     */
    public static function terms($field, $terms)
    {
        return [
            'terms' => [
                $field => $terms
            ]
        ];
    }

    /**
     * @param string $field
     * @param string $term
     * @return array
     */
    public static function term($field, $term)
    {
        return [
            'term' => [
                $field => $term
            ]
        ];
    }

    /**
     * @param string $field
     * @param string|int $gte greater than or equal
     * @param string|int $lte less than or equal
     * @param array $options options to pass into the range query
     * @return array
     */
    public static function range($field, $gte = '', $lte = '', $options = [])
    {
        if ($gte !== '') {
            $options['gte'] = $gte;
        }
        if ($lte !== '') {
            $options['lte'] = $lte;
        }
        return [
            'range' => [
                $field => $options
            ]
        ];
    }

    /**
     * @param string $path
     * @param string $query
     * @return array
     */
    public static function nest($path, $query = '')
    {
        return [
            'nested' => [
                'path' => $path,
                'query' => self::query($query)
            ]
        ];
    }

    /**
     * @param string $field
     * @param string $searchQuery
     * @return array
     */
    public static function fullWildcard($field, $searchQuery)
    {
        return [
            'wildcard' => [
                $field => "*$searchQuery*"
            ]
        ];
    }

    /**
     * expects * somewhere in the string, if at the end, might as well just use prefix instead
     * @param string $field
     * @param string $searchQuery
     * @return array
     */
    public static function wildcard($field, $searchQuery)
    {
        return [
            'wildcard' => [
                $field => "$searchQuery"
            ]
        ];
    }

    /**
     * @param string $field
     * @param string $searchQuery
     * @return array
     */
    public static function suggest($field, $searchQuery)
    {
        $prefix = self::prefix($field, $searchQuery);
        return [
            'suggest' => $prefix
        ];
    }


    /**
     * @param string $field
     * @param string|array $searchTerms
     * @return array
     */
    public static function prefix($field, $searchTerms)
    {
        return [
            'prefix' => [
                $field => $searchTerms
            ]
        ];
    }

    /**
     * @param array $fields
     * @param string $query
     * @param string $type
     * @param int $max_expansions
     * @return array
     */
    public static function multiMatch($fields, $query, $type, $max_expansions)
    {
        return [
            'multi_match' => [
                'query' => $query,
                'fields' => $fields,
                'type' => $type,
                'max_expansions' => $max_expansions,
            ],
        ];
    }
    
    public static function exists($field)
    {
        return [
            'exists' => [
                'field' => $field,
            ],
        ];
    }

    /**
     * @param string $query
     * @return string
     */
    public static function query($query = '')
    {
        return empty($query) ? ["match_all" => (object) []] : $query;
    }
}
