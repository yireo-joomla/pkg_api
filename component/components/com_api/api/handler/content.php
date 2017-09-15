<?php

class ContentApiHandler extends ApiHandler
{
    protected $allowedMethods = [
        'ContentModelArticles' => [
            'getItems',
        ]
    ];
}