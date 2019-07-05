<?php
namespace amin3mej\graphql;

use Yii;
use yii\base\InvalidConfigException;
use yii\data\BaseDataProvider;
use yii\di\Instance;
use yii\helpers\ArrayHelper;


class GraphqlDataProvider extends BaseDataProvider
{
    /**
     * @var string the GraphQL query that is used to fetch data models
     */
    public $query;

    /**
     * @var string the address for access data in GraphQL response
     */
    public $queryCallback;

    /**
     * @var string the GraphQL query that is used to fetch [[totalCount]]
     */
    public $totalCountQuery;

    /**
     * @var string|null the key for defaultTarget array or if its null, defaultTarget will be used.
     */
    public $target;

    /**
     * @var GraphqlQuery
     */
    public $graphqlQuery;

    public function init()
    {

        if ($this->graphqlQuery !== NULL) {
            $this->graphqlQuery = Instance::ensure($this->graphqlQuery, GraphqlQuery::className());
        } elseif (isset(Yii::$app->graphql)) {
            $this->graphqlQuery = Yii::$app->graphql;
        } else {
            throw new InvalidConfigException('GraphqlDataProvider::graphqlQuery or Config::graphql must be set.');
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareModels()
    {
        if (!$this->query) {
            throw new InvalidConfigException('The "query" property must be set.');
        }

        $variables = [];

        if (($pagination = $this->getPagination()) !== false) {
            $pagination->totalCount = $this->getTotalCount();
            if ($pagination->totalCount === 0) {
                return [];
            }
            $variables['limit'] = $pagination->getLimit();
            $variables['offset'] = $pagination->getOffset();
        }
        //@TODO: sort must be implemented.

        $response = $this->graphqlQuery->execute($this->query, $variables, $this->target);
        return ArrayHelper::getValue($response, $this->queryCallback);
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareKeys($models)
    {
        return array_keys($models);
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareTotalCount()
    {
        if (!$this->totalCountQuery) {
            throw new InvalidConfigException('The "totalCountQuery" property must be set.');
        }
        
        $response = $this->graphqlQuery->execute($this->totalCountQuery, [], $this->target);
        $return = [];
        array_walk_recursive($response, function($a) use (&$return) { $return[] = $a; });
        
        return $return[0];
    }
}