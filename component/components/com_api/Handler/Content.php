<?php
namespace Api\Handler;

use Api\Handler;

class Content extends Handler
{
    protected $allowedMethods = [
        'ContentModelArticles' => [
            'getItems',
        ],
        'ContentModelArticle' => [
            'getItem',
        ]
    ];
}
