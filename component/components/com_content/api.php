<?php
class ContentApiHandler extends ApiHandler implements ApiHandlerInterface
{
    protected function getModel(string $modelName, int $id = 0)
    {
        // API request "/MENU_ITEM_ALIAS/content/foobar"
        if ($modelName === 'foobar') {
            $modelName = 'articles';
        }

        return parent::getModel($modelName, $id);
    }
}