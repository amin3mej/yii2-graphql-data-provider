<?php
namespace amin3mej\graphql;


use yii\base\Component;
use yii\base\InvalidConfigException;

class GraphqlQuery extends Component
{
    /**
     * @var array|string List of available addresses for connecting or default address to connect
     */
    public $defaultTarget;

    /**
     * @var array Custom headers to send with query,
     * Use 1d for common headers and use 2d for customer per target headers.
     */
    public $customHeaders;

    public function init()
    {
        if (!is_array($this->customHeaders))
            $this->customHeaders = [];
    }

    /**
     * @param string $query The GraphQL query for executing
     * @param array $variables The variables that binds to query
     * @param string|null $target Target for GraphQL query. if [[target]] be is null, [[defaultTarget]] is used.
     * @return array The response of server
     * @throws InvalidConfigException
     */
    public function execute($query, $variables = [], $target = null)
    {
        $url = $this->getUrl($target);
        $headers = $this->getHeaders($target);

        $content = [
            'query' => $query,
        ];
        if($variables)
            $content['variables'] = $variables;

        $content = json_encode($content);
        array_push($headers, 'Content-Length: ' . strlen($content));

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_TIMEOUT => 5,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => $content
        ]);
        $result = curl_exec($curl);
        curl_close($curl);
        return json_decode($result, true);
    }

    /**
     * @param $target The key in defaultTarget or Url
     * @return string Valid url for start executing
     * @throws InvalidConfigException
     */
    private function getUrl($target) {
        if (filter_var($target, FILTER_VALIDATE_URL))
           return $target;

        // I use isset instead of in_array becuase isset is faster
        elseif (is_array($this->defaultTarget) && isset($this->defaultTarget[$target]))
            return $this->defaultTarget[$target];

        elseif (is_string($this->defaultTarget))
            return $this->defaultTarget;

        throw new InvalidConfigException('GraphqlQuery::defaultTarget or $target parameter for GraphqlQuery::execute must be set.');
    }

    /**
     * @param $target
     * @return array List of headers, which probably is customized per [[target]]
     */
    private function getHeaders($target)
    {
        $customCommonHeaders = array_filter($this->customHeaders, function ($item) {
           return !is_array($item);
        });

        $customSpecificHeaders = isset($this->customHeaders[$target]) ? $this->customHeaders[$target] : [];

        $headers = array_merge([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ], $customCommonHeaders, $customSpecificHeaders);

        return array_map(function($k, $v){
            return "$k: $v";
        }, array_keys($headers), array_values($headers));
    }
}