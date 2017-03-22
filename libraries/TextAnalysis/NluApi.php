<?php
/**
 * Client for IBM's Natural Language Understanding service API.
 *
 * @see https://www.ibm.com/watson/developercloud/natural-language-understanding.html
 */
class TextAnalysis_NluApi
{
    const ENDPOINT = 'https://gateway.watsonplatform.net/natural-language-understanding/api/v1';
    const VERSION = '2017-02-27';

    protected $username;
    protected $password;

    public function __construct($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    public function entities($text, array $options = array())
    {
        $params = array(
            'text' => $text,
            'features' => array(
                'entities' => $options,
            ),
        );
        return $this->request($params);
    }

    public function categories($text, array $options = array())
    {
        $params = array(
            'text' => $text,
            'features' => array(
                'categories' => $options,
            ),
        );
        return $this->request($params);
    }

    public function concepts($text, array $options = array())
    {
        $params = array(
            'text' => $text,
            'features' => array(
                'concepts' => $options,
            ),
        );
        return $this->request($params);
    }

    public function keywords($text, array $options = array())
    {
        $params = array(
            'text' => $text,
            'features' => array(
                'keywords' => $options,
            ),
        );
        return $this->request($params);
    }

    public function combined($text, array $features = array())
    {
        $params = array(
            'text' => $text,
            'features' => $features,
        );
        return $this->request($params);
    }

    protected function request(array $params)
    {
        $client = new Zend_Http_Client;
        $client->setUri(sprintf('%s/analyze', self::ENDPOINT))
            ->setMethod(Zend_Http_Client::POST)
            ->setAuth($this->username, $this->password)
            ->setHeaders('Content-Type', 'application/json')
            ->setParameterGet('version', self::VERSION)
            ->setRawData(json_encode($params, JSON_FORCE_OBJECT))
            ->setEncType('application/json');
        return $client->request();
    }
}
