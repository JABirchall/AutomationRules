<?php


namespace IPS\rules\modules\front\system;

/* To prevent PHP errors (extending class does not exist) revealing path */
if (!defined('\IPS\SUITE_UNIQUE_KEY')) {
    header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}

/**
 * ajax
 */
class _ajax extends \IPS\Dispatcher\Controller
{
    /**
     * Execute
     *
     * @return    void
     */
    public function execute()
    {
        parent::execute();
    }

    /**
     * ...
     *
     * @return    void
     */
    protected function manage()
    {
        // This is the default method if no 'do' parameter is specified
    }

    /**
     * Content Autocomplete Lookup
     *
     * @retun    void
     */
    public function findContent()
    {
        $results = [];
        $input = mb_strtolower(\IPS\Request::i()->input);
        $contentClass = str_replace('-', '\\', \IPS\Request::i()->class);

        if (!is_subclass_of($contentClass, '\IPS\Content\Item')) {
            \IPS\Output::i()->json([]);
        }

        $idField = $contentClass::$databaseColumnId;
        $sqlTitle = $contentClass::$databaseTable . '.' . $contentClass::$databasePrefix . $contentClass::$databaseColumnMap['title'];

        $where = ["{$sqlTitle} LIKE ?", '%' . $input . '%'];

        foreach ($contentClass::getItemsWithPermission([$where], null, 20) as $content) {
            $results[] = [
                'value' => 'ID:' . $content->$idField . ' - ' . $content->mapped('title'),
                'name' => $content->mapped('title'),
                'extra' => 'ID:' . $content->$idField . ' / Author: ' . $content->author()->name,
                'photo' => $content->author()->photo,
            ];
        }

        \IPS\Output::i()->json($results);
    }

}