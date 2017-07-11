<?php
namespace Crowdskout\ElasticsearchQueryBuilder\Agg;

interface AggregationInterface
{
    /**
     * Generate the query to be submitted to Elasticsearch
     *
     * @return array
     */
    public function generateQuery();

    /**
     * Return an array representing the parsed results
     *
     * @param array $results raw results from Elasticsearch
     * @return array
     */
    public function generateResults($results);
}
