<?php
class TextAnalysis_AlchemyApi
{
    const BASE_URL = 'http://access.alchemyapi.com/calls';

    protected $apiKey;

    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function entities($text, array $options = array())
    {
        $options['text'] = $text;
        $options['sentiment'] = 1; // default 0
        $options['showSourceText'] = 1; // default 0
        return $this->request('/text/TextGetRankedNamedEntities', $options);
    }

    public function concepts($text, array $options = array())
    {
        $options['text'] = $text;
        return $this->request('/text/TextGetRankedConcepts', $options);
    }

    public function taxonomy($text, array $options = array())
    {
        $options['text'] = $text;
        return $this->request('/text/TextGetRankedTaxonomy', $options);
    }

    public function keywords($text, array $options = array())
    {
        $options['text'] = $text;
        return $this->request('/text/TextGetRankedKeywords', $options);
    }

    public function relations($text, array $options = array())
    {
        $options['text'] = $text;
        return $this->request('/text/TextGetRelations', $options);
    }

    public function combined($text, array $options = array())
    {
        $options['text'] = $text;
        $options['sentiment'] = 1;
        return $this->request('/text/TextGetCombinedData', $options);
    }

    protected function request($endpoint, array $params)
    {
        $endpoint = self::BASE_URL . $endpoint;
        $params['apikey'] = $this->apiKey;
        $params['outputMode'] = 'json';

        $client = new Zend_Http_Client;
        $client->setUri($endpoint)
            ->setMethod(Zend_Http_Client::POST)
            ->setHeaders('Content-Type', 'application/x-www-form-urlencoded')
            ->setParameterPost($params);
        return $client->request();
    }
}
