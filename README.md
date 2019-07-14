<p align="center">
    <a href="https://github.com/yiisoft" target="_blank">
        <img src="https://avatars0.githubusercontent.com/u/993323" height="100px">
    </a>
    <h1 align="center">Yii2 GraphQL Data-Provider Extension</h1>
    <br>
</p>

A helper for GraphQL that include QueryHelper and ActiveDataProvider for Yii2.

[![Latest Stable Version](https://poser.pugx.org/amin3mej/yii2-graphql-data-provider/v/stable)](https://packagist.org/packages/amin3mej/yii2-graphql-data-provider) [![Total Downloads](https://poser.pugx.org/amin3mej/yii2-graphql-data-provider/downloads)](https://packagist.org/packages/amin3mej/yii2-graphql-data-provider) [![License](https://poser.pugx.org/amin3mej/yii2-graphql-data-provider/license)](https://packagist.org/packages/amin3mej/yii2-graphql-data-provider)

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```sh
php composer.phar require --prefer-dist amin3mej/yii2-graphql-data-provider "*"
```

or add

```
"amin3mej/yii2-graphql-data-provider": "*"
```

to the require section of your composer.json.

Configuration
-------------

**Component Setup**

To access Query Component, you need to configure the components array in your application configuration:
```php
'components' => [
    'graphql' => [
        'class' => 'amin3mej\graphql\GraphqlQuery',
        'defaultTarget' => [
            'github' => 'https://api.github.com/graphql',
            'graphqlhub' => 'https://www.graphqlhub.com/graphql',
        ],
        'customHeaders' => [
            'Content-Type' => 'application/json',
            'github' => [
                'Authorization' => 'Bearer ' . $params['github.graphqlToken'],
            ],
        ],
    ],
],
```

You can define your targets here to access them fast from your code.

Also you can define some headers here to add to the requests. if you fine a key directly in array, the key recognized as common header and It will send with all request.
if you define an array with the same name with your target, it headers will used only for the target.

Usage:
---------

Query:

```php
const QUERY_CHECK = <<<QUERY
query test (\$userId: Int!){
  userInfo (userId: \$userId) {
		firstname
		lastname
    email
  }
}
QUERY;

$result = Yii::$app->graphql->execute(QUERY_CHECK, ['userId' => (int) $userId], 'github');
```



ActiveDataProvider:

```php
use amin3mej\graphql\GraphqlDataProvider;

// If you want to use pagination in ActiveDataProvider, Set $offset and $limit in your query. Everything will be handled automatically.
const QUERY = <<<QUERY
query(\$limit: Int, \$offset: Int){
  categories (first: \$limit, skip: \$offset){
    id
    name
    icon
  }
}
QUERY;

$dataProvider = new GraphqlDataProvider([
    'query' => QUERY,
    'queryCallback' => 'data.categories', // How to access the array in responded query result? More: https://www.yiiframework.com/doc/guide/2.0/en/helper-array#getting-values
    'totalCountQuery' => 'query { categoriesConnection { aggregate { count } } }',
    'target' => 'prisma',
]);

return $this->render('index', [
    'dataProvider' => $dataProvider,
]);
```

