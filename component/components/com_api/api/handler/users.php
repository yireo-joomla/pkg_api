<?php

class UsersApiHandler extends ApiHandler
{
    protected $allowedMethods = [
        'UsersModelUsers' => [
            'getItems',
        ],
        'UsersModelUser' => [
            'getItem',
        ]
    ];

    protected function makeMethodCallback(string $requestType, $model)
    {
        $items = parent::makeMethodCallback($requestType, $model);

        if ($model == 'UsersModelUsers') {
            foreach ($items as $itemIndex => $item) {
                if (isset($item->password)) {
                    unset($item->password);
                }

                if (isset($item->params)) {
                    unset($item->params);
                }

                $items[$itemIndex] = $item;
            }
        }

        return $items;
    }
}