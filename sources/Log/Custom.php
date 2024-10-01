<?php
/**
 * @brief        IPS4 Rules
 * @author        Kevin Carwile (http://www.linkedin.com/in/kevincarwile)
 * @copyright        (c) 2014 - Kevin Carwile
 * @package        Rules
 * @since        6 Feb 2015
 */


namespace IPS\rules\Log;

/* To prevent PHP errors (extending class does not exist) revealing path */
if (!defined('\IPS\SUITE_UNIQUE_KEY')) {
    header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}

/**
 * Node
 */
class _Custom extends \IPS\Node\Model implements \IPS\Node\Permissions
{
    /**
     * @brief    [ActiveRecord] Multiton Store
     */
    protected static $multitons;

    /**
     * @brief    [ActiveRecord] Database Table
     */
    public static $databaseTable = 'rules_custom_logs';

    /**
     * @brief    [ActiveRecord] Database Prefix
     */
    public static $databasePrefix = 'custom_log_';

    /**
     * @brief    [Node] Order Database Column
     */
    public static $databaseColumnOrder = 'weight';

    /**
     * @brief    [ActiveRecord] Database ID Fields
     */
    protected static $databaseIdFields = ['custom_log_key'];

    /**
     * @brief    [Node] Parent ID Database Column
     */
    public static $databaseColumnParent = null;

    /**
     * @brief    Sub Node Class
     */
    public static $subnodeClass = '\IPS\rules\Log\Argument';

    /**
     * @brief    [Node] Node Title
     */
    public static $nodeTitle = 'custom_logs';

    /**
     * @brief    [Node] App for permission index
     */
    public static $permApp = 'rules';

    /**
     * @brief    [Node] Type for permission index
     */
    public static $permType = 'custom_log';

    /**
     * @brief    The map of permission columns
     */
    public static $permissionMap = [
        'view' => 'view',
        'delete' => 2,
    ];

    /**
     * @brief    [Node] Prefix string that is automatically prepended to permission matrix language strings
     */
    public static $permissionLangPrefix = 'rules_log_';

    /**
     * @brief    Use Modal Forms?
     */
    public static $modalForms = false;

    /**
     *  Disable Copy Button
     */
    public $noCopyButton = true;

    /**
     *  Get Title
     */
    public function get__title()
    {
        return $this->title;
    }

    /**
     * Set Title
     */
    public function set__title($val)
    {
        $this->title = $val;
    }

    /**
     * Get Node Description
     */
    public function get__description()
    {
        return $this->description;
    }

    /**
     * Get Description
     */
    public function get_description()
    {
        return isset($this->_data['description']) ? $this->_data['description'] : '';
    }

    /**
     * Set Description
     */
    public function set_description($val)
    {
        $this->_data['description'] = $val;
    }

    /**
     * Get Data
     */
    public $data = [];

    /**
     * [Node] Get whether or not this node is enabled
     *
     * @note    Return value NULL indicates the node cannot be enabled/disabled
     * @return    bool|null
     */
    protected function get__enabled()
    {
        return $this->enabled;
    }

    /**
     * [Node] Set whether or not this node is enabled
     *
     * @param bool|int $enabled Whether to set it enabled or disabled
     * @return    void
     */
    protected function set__enabled($enabled)
    {
        $this->enabled = $enabled;
    }

    /**
     * @brief    Action Definition
     */
    public $definition = null;

    /**
     * Init
     *
     * @return    void
     */
    public function init()
    {
    }

    /**
     * [Node] Get buttons to display in tree
     * Example code explains return value
     *
     * @param string $url Base URL
     * @param bool $subnode Is this a subnode?
     * @return    array
     */
    public function getButtons($url, $subnode = false)
    {
        $buttons = parent::getButtons($url, $subnode);

        $delete = $buttons['delete'];
        unset($buttons['delete']);

        $buttons['view'] = [
            'icon' => 'eye',
            'title' => 'rules_view_custom_log',
            'link' => \IPS\Http\Url::internal("app=rules&module=rules&controller=logs")->setQueryString(
                ['tab' => 'log_' . $this->id]
            ),
            'data' => [],
        ];

        if ($this->can('delete')) {
            $buttons['flush'] = [
                'icon' => 'trash',
                'title' => 'rules_flush_custom_log',
                'link' => \IPS\Http\Url::internal(
                    "app=rules&module=rules&controller=customlogs&do=flush"
                )->setQueryString(['log_id' => $this->id])->csrf(),
                'data' => [
                    'confirm' => '',
                    'confirmMessage' => \IPS\Member::loggedIn()->language()->addToStack('rules_flush_confirm'),
                ],
            ];
        }

        if ($this->canDelete()) {
            $buttons['delete'] = $delete;
        }

        return $buttons;
    }

    /**
     * Can Copy?
     */
    public function canCopy()
    {
        return false;
    }

    /**
     * [Node] Custom Badge
     *
     * @return    NULL|array    Null for no badge, or an array of badge data (0 => CSS class type, 1 => language string, 2 => optional raw HTML to show instead of language string)
     */
    protected function get__badge()
    {
        return [
            0 => 'ipsBadge ipsBadge_neutral',
            1 => \IPS\Db::i()->select('COUNT(*)', static::getTableName($this->class), ['log_id=?', $this->id]
            )->first(),
        ];
    }

    /**
     * [Node] Add/Edit Form
     *
     * @param \IPS\Helpers\Form $form The form
     * @return    void
     */
    public function form(&$form)
    {
        $lang = \IPS\Member::loggedIn()->language();
        $wrap_chosen_prefix = "<div data-controller='rules.admin.ui.chosen'>";
        $wrap_chosen_suffix = "</div>";

        /**
         * Basic Object Classes
         */
        $object_classes = [// None
        ];
        $object_classes_toggles = [];
        $object_classes_containers = [];

        $core_key = $lang->get('__app_core');

        /**
         * Add additional content types
         */
        foreach (\IPS\Application::allExtensions('core', 'ContentRouter') as $router) {
            $appname = '';
            $_object_classes = [];
            foreach ($router->classes as $contentItemClass) {
                if (is_subclass_of($contentItemClass, '\IPS\Content\Item')) {
                    /* Set Appname */
                    $appname = $appname ?: $lang->addToStack('__app_' . $contentItemClass::$application);
                    if ($contentItemClass::$application == 'core') {
                        $core_key = $appname;
                    }

                    /* Add the content class */
                    $_object_classes['-' . str_replace('\\', '-', $contentItemClass)] = ucwords(
                            $lang->checkKeyExists($contentItemClass::$title) ? $lang->get(
                                $contentItemClass::$title
                            ) : ''
                        ) . ' ( ' . $contentItemClass . ' )';

                    /* Add node class */
                    if (isset($contentItemClass::$containerNodeClass) and $nodeClass = $contentItemClass::$containerNodeClass) {
                        $_object_classes['-' . str_replace('\\', '-', $nodeClass)] = $lang->addToStack(
                                $nodeClass::$nodeTitle
                            ) . ' ( ' . $nodeClass . ' )';

                        $lang->words['containers-' . str_replace('\\', '-', $nodeClass)] = $lang->get(
                            $nodeClass::$nodeTitle
                        );
                        $object_classes_containers[] = new \IPS\Helpers\Form\Node(
                            'containers-' . str_replace('\\', '-', $nodeClass),
                            isset(
                                $configuration['containers-' . str_replace(
                                    '\\',
                                    '-',
                                    $nodeClass
                                )]
                            ) ? $configuration['containers-' . str_replace('\\', '-', $nodeClass)] : 0,
                            false,
                            ['class' => $nodeClass, 'multiple' => true, 'subnodes' => false, 'zeroVal' => 'All'],
                            null,
                            null,
                            null,
                            'containers-' . str_replace('\\', '-', $nodeClass)
                        );
                        $object_classes_toggles['-' . str_replace(
                            '\\',
                            '-',
                            $contentItemClass
                        )] = ['containers-' . str_replace('\\', '-', $nodeClass)];
                        $object_classes_toggles['-' . str_replace(
                            '\\',
                            '-',
                            $nodeClass
                        )] = ['containers-' . str_replace('\\', '-', $nodeClass)];
                    }
                }
            }

            $object_classes[$appname] = $_object_classes;
        }

        $data_classes = [
            $core_key => [
                '-IPS-Member' => 'Member ( IPS\Member )',
            ],
        ];

        $data_classes = array_replace_recursive($data_classes, $object_classes);

        $form->add(new \IPS\Helpers\Form\Text('custom_log_title', $this->title, true));
        $form->add(
            new \IPS\Helpers\Form\Select(
                'custom_log_class',
                $this->class ?: '-IPS-Member',
                false,
                [
                    'options' => $data_classes,
                    'disabled' => $this->class !== null,
                    'toggles' => $object_classes_toggles,
                ],
                null,
                $wrap_chosen_prefix,
                $wrap_chosen_suffix,
                'data_class'
            )
        );
        $form->add(new \IPS\Helpers\Form\TextArea('custom_log_description', $this->description, false));

        $form->addHeader('custom_log_options');

        $sort_options = ['id' => 'custom_log_logtime'];

        if ($this->id) {
            foreach ($this->children() as $data) {
                if (in_array($data->type, ['int', 'float', 'string'])) {
                    $sort_options[$data->varname] = $data->name;
                }
            }
        }

        $form->add(
            new \IPS\Helpers\Form\Select(
                'custom_log_sortby',
                $this->sortby ?: 'id',
                true,
                ['options' => $sort_options],
                null,
                $wrap_chosen_prefix,
                $wrap_chosen_suffix
            )
        );
        $form->add(
            new \IPS\Helpers\Form\Select(
                'custom_log_sortdir',
                $this->sortdir ?: 'desc',
                true,
                ['options' => ['desc' => 'Descending', 'asc' => 'Ascending']],
                null,
                $wrap_chosen_prefix,
                $wrap_chosen_suffix
            )
        );
        $form->add(new \IPS\Helpers\Form\YesNo('custom_log_display_empty', $this->display_empty, true, []));
        $form->add(
            new \IPS\Helpers\Form\Number('custom_log_max_logs', $this->max_logs ?: 0, true, ['unlimited' => 0])
        );
        $form->add(
            new \IPS\Helpers\Form\Number('custom_log_entity_max', $this->entity_max ?: 0, true, ['unlimited' => 0])
        );
        $form->add(
            new \IPS\Helpers\Form\Number('custom_log_max_age', $this->max_age ?: 0, true, ['unlimited' => 0])
        );
        $form->add(new \IPS\Helpers\Form\Number('custom_log_limit', $this->limit ?: 25, true, ['min' => 1]));

        $form->add(
            new \IPS\Helpers\Form\YesNo(
                'custom_log_display_time',
                $this->display_time !== null ? $this->display_time : true,
                false,
                ['togglesOn' => ['custom_log_lang_time']]
            )
        );
        $form->add(
            new \IPS\Helpers\Form\Text(
                'custom_log_lang_time',
                $this->lang_time !== null ? $this->lang_time : \IPS\Member::loggedIn()->language()->get(
                    'rules_log_lang_time'
                ),
                false,
                ['placeholder' => \IPS\Member::loggedIn()->language()->get('rules_log_lang_time')],
                null,
                null,
                null,
                'custom_log_lang_time'
            )
        );
        $form->add(
            new \IPS\Helpers\Form\Text(
                'custom_log_lang_message',
                $this->lang_message !== null ? $this->lang_message : \IPS\Member::loggedIn()->language()->get(
                    'rules_log_lang_message'
                ),
                false,
                ['placeholder' => \IPS\Member::loggedIn()->language()->get('rules_log_lang_message')],
                null,
                null,
                null,
                'custom_log_lang_message'
            )
        );


        parent::form($form);
    }

    /**
     * Form to delete or move content
     *
     * @param bool $showMoveToChildren If TRUE, will show "move to children" even if there are no children
     * @return    \IPS\Helpers\Form
     */
    public function deleteOrMoveForm($showMoveToChildren = false)
    {
        $form = new \IPS\Helpers\Form('delete_custom_action', 'rules_confirm_delete');
        $form->hiddenValues['node_move_children'] = 0;
        return $form;
    }

    /**
     * [Node] Save Add/Edit Form
     *
     * @param array $values Values from the form
     * @return    void
     */
    public function saveForm($values)
    {
        /**
         * Prevent changing of the log association.
         */
        if ($this->id) {
            unset($values['custom_log_class']);
        }

        parent::saveForm($values);
    }

    /**
     * [ActiveRecord] Save
     */
    public function save()
    {
        /**
         * To link custom actions with rules that use them
         * after export/import, we need to use a static
         * sync key since ID's will change from system to
         * system.
         */
        if (!$this->key) {
            $this->key = md5(uniqid() . mt_rand());
        }

        /**
         * Create a data table if we don't already have one
         */
        if (!\IPS\Db::i()->checkForTable($this::getTableName($this->class))) {
            \IPS\Db::i()->createTable($this::tableDefinition($this->class));
        }

        parent::save();
    }

    /**
     * Get Table Name
     */
    public static function getTableName($class)
    {
        $class = str_replace('\\', '-', $class);
        $class = trim($class, '-');
        $table_suffix = mb_strtolower($class);
        $table_suffix = str_replace('ips-', '', $table_suffix);
        $table_suffix = str_replace('-', '_', $table_suffix);

        return 'rules_logs_' . $table_suffix;
    }

    /**
     * Get Table Definition
     */
    public static function tableDefinition($class)
    {
        $table_name = static::getTableName($class);

        return [
            'name' => $table_name,
            'columns' => [
                'id' => [
                    'name' => 'id',
                    'type' => 'int',
                    'allow_null' => false,
                    'auto_increment' => true,
                    'binary' => false,
                    'comment' => '',
                    'decimals' => null,
                    'default' => null,
                    'length' => 20,
                    'unsigned' => false,
                    'values' => [],
                    'zerofill' => false,
                ],
                'log_id' => [
                    'name' => 'log_id',
                    'type' => 'int',
                    'allow_null' => false,
                    'auto_increment' => false,
                    'binary' => false,
                    'comment' => '',
                    'decimals' => null,
                    'default' => null,
                    'length' => 20,
                    'unsigned' => false,
                    'values' => [],
                    'zerofill' => false,
                ],
                'entity_id' => [
                    'name' => 'entity_id',
                    'type' => 'int',
                    'allow_null' => false,
                    'auto_increment' => false,
                    'binary' => false,
                    'comment' => '',
                    'decimals' => null,
                    'default' => 0,
                    'length' => 20,
                    'unsigned' => false,
                    'values' => [],
                    'zerofill' => false,
                ],
                'logtime' => [
                    'name' => 'logtime',
                    'type' => 'int',
                    'allow_null' => false,
                    'auto_increment' => false,
                    'binary' => false,
                    'comment' => '',
                    'decimals' => null,
                    'default' => null,
                    'length' => 11,
                    'unsigned' => false,
                    'values' => [],
                    'zerofill' => false,
                ],
                'message' => [
                    'name' => 'message',
                    'type' => 'varchar',
                    'allow_null' => false,
                    'auto_increment' => false,
                    'binary' => false,
                    'comment' => '',
                    'decimals' => null,
                    'default' => null,
                    'length' => 2048,
                    'unsigned' => false,
                    'values' => [],
                    'zerofill' => false,
                ],
            ],
            'indexes' => [
                'PRIMARY' => [
                    'type' => 'primary',
                    'name' => 'PRIMARY',
                    'length' => [null],
                    'columns' => ['id'],
                ],
                'LOG' => [
                    'type' => 'key',
                    'name' => 'LOG',
                    'length' => [null],
                    'columns' => ['log_id'],
                ],
                'ENTITY' => [
                    'type' => 'key',
                    'name' => 'ENTITY',
                    'length' => [null],
                    'columns' => ['log_id', 'entity_id'],
                ],
            ],
        ];
    }

    /**
     * Clone
     */
    public function __clone()
    {
        $this->key = md5(uniqid() . mt_rand());
        parent::__clone();
    }

    /**
     * [ActiveRecord] Delete Record
     *
     * @return    void
     */
    public function delete()
    {
        foreach ($this->children() as $argument) {
            $argument->delete();
        }

        /* Delete existing logs */
        \IPS\Db::i()->delete($this::getTableName($this->class), ['log_id=?', $this->_id]);

        $result = parent::delete();

        /* If there are no custom logs left, drop the table too */
        if (!\IPS\Db::i()->select('COUNT(*)', 'rules_custom_logs', ['custom_log_class=?', $this->class])->first()) {
            try {
                \IPS\Db::i()->dropTable($this::getTableName($this->class));
            } catch (\IPS\Db\Exception $e) {
            }
        }

        return $result;
    }

    /**
     * Add Log Entry
     *
     * @param object $entity The entity the log is associated with
     * @param string $message The message to log
     * @param array $data Additional data to log
     * @return    bool                Returns TRUE if log is created, FALSE if log is disabled
     * @throws    \InvalidArgumentException
     */
    public function createEntry($entity, $message, $data = [])
    {
        if (!$this->enabled) {
            return false;
        }

        if (!is_object($entity) or get_class($entity) !== ltrim(str_replace('-', '\\', $this->class), '\\')) {
            throw new \InvalidArgumentException(
                'Invalid log association. Type: ' . (is_object($entity) ? get_class($entity) : gettype($entity))
            );
        }

        /* Create basic log entry */
        $logEntry = [
            'log_id' => $this->_id,
            'logtime' => time(),
            'entity_id' => $entity->activeid,
            'message' => $message,
        ];

        /* Attach the custom log data */
        foreach ($data as $key => $value) {
            $logValue = $value;

            if (is_object($value)) {
                if (\IPS\rules\Data::isConcreteRecord(get_class($value))) {
                    $logValue = $value->activeid;
                } else {
                    $logValue = json_encode(\IPS\rules\Application::storeArg($value));
                }
            }

            if (is_array($value)) {
                $logValue = json_encode(\IPS\rules\Application::storeArg($value));
            }

            $logEntry['data_' . $key] = $logValue;
        }

        \IPS\Db::i()->insert(static::getTableName($this->class), $logEntry);

        /* Prune old logs */
        $this->prune($entity);

        return true;
    }

    /**
     * Prune Logs
     *
     * @param object $entity The entity to prune logs for
     * @return    void
     */
    public function prune($entity = null)
    {
        /* Prune overflow */
        if ($this->max_logs) {
            try {
                $cutoff = \IPS\Db::i()->select(
                    'id', static::getTableName($this->class), ['log_id=?', $this->id],
                    'id DESC', [$this->max_logs, 1]
                )->first();
                \IPS\Db::i()->delete(static::getTableName($this->class), ['id<=? AND log_id=?', $cutoff, $this->id]
                );
            } catch (\UnderflowException $e) {
            }
        }

        /* Prune old logs */
        if ($this->max_age) {
            $oldTimer = $this->max_age * (60 * 60 * 24);
            \IPS\Db::i()->delete(
                static::getTableName($this->class),
                ['log_id=? AND logtime<?', $this->id, $oldTimer]
            );
        }

        if ($entity) {
            /* Prune entity logs */
            if ($this->entity_max) {
                try {
                    $cutoff = \IPS\Db::i()->select(
                        'id', static::getTableName($this->class),
                        ['log_id=? AND entity_id=?', $this->id, $entity->activeid], 'id DESC',
                        [$this->entity_max, 1]
                    )->first();
                    \IPS\Db::i()->delete(
                        static::getTableName($this->class),
                        ['id<=? AND log_id=? AND entity_id=?', $cutoff, $this->id, $entity->activeid]
                    );
                } catch (\UnderflowException $e) {
                }
            }
        }
    }

    /**
     * Flush Logs
     *
     * @param object $entity The entity to flush logs for
     * @return    void
     */
    public function flush($entity = null)
    {
        if ($entity) {
            /* Flush entity logs */
            \IPS\Db::i()->delete(
                static::getTableName($this->class),
                ['log_id=? AND entity_id=?', $this->id, $entity->activeid]
            );
        } else {
            /* Flush all logs */
            \IPS\Db::i()->delete(static::getTableName($this->class), ['log_id=?', $this->id]);
        }
    }

    /**
     * Check for logs
     *
     * @param object $entity The entity to count logs for
     */
    public function logCount($entity)
    {
        return \IPS\Db::i()->select(
            'COUNT(*)', static::getTableName($this->class),
            ['log_id=? AND entity_id=?', $this->id, $entity->activeid]
        )->first();
    }

    /**
     * Table JS Controller Flag
     */
    protected static $tableControllerLoaded = false;

    /**
     * Get logs for an entity
     *
     * @param object|NULL $entity The entity to get the log table for or NULL for all entities
     * @param int|NULL $limit Override for the log page size
     * @param
     */
    public function logsTable($entity = null, $limit = null)
    {
        /* Set table defaults */
        $sortBy = \IPS\Db::i()->checkForColumn(
            static::getTableName($this->class),
            'data_' . $this->sortby
        ) ? 'data_' . $this->sortby : 'id';
        $sortDirection = $this->sortdir ?: 'desc';
        $page = 1;

        /**
         * Process log table requests
         */
        if (\IPS\Request::i()->log) {
            /* This log is targeted */
            if ($this->id == \IPS\Request::i()->log) {
                /* Update table parameters */
                $sortBy = \IPS\Request::i()->logsortby ?: (\IPS\Request::i()->sortby ?: $sortBy);
                $sortDirection = \IPS\Request::i()->logsortdir ?: (\IPS\Request::i()->sortdirection ?: $sortDirection);
                $page = \IPS\Request::i()->logpage ?: (\IPS\Request::i()->page ?: $page);

                /**
                 * Process Commands
                 */
                switch (\IPS\Request::i()->logdo) {
                    case 'delete':

                        if ($this->can('delete') and \IPS\Request::i()->logid) {
                            \IPS\Db::i()->delete(
                                static::getTableName($this->class),
                                ['id=?', \IPS\Request::i()->logid]
                            );
                            if (\IPS\Request::i()->isAjax()) {
                                \IPS\Output::i()->json('OK');
                            }
                        }
                }
            }
        }

        /**
         * Build the table
         */
        $self = $this;
        $lang = \IPS\Member::loggedIn()->language();
        $lang->words['rules_custom_logs_table_' . $this->id . '_logtime'] = $this->lang_time !== null ? $this->lang_time : $lang->get(
            'rules_log_lang_time'
        );
        $lang->words['rules_custom_logs_table_' . $this->id . '_message'] = $this->lang_message !== null ? $this->lang_message : $lang->get(
            'rules_log_lang_message'
        );
        $lang->words['rules_custom_logs_table_' . $this->id . '_entity_id'] = $lang->get('rules_log_lang_entity_id');

        /**
         * On the front end, our own controller is used to avoid conflicts with other paginated tables/content on the page.
         * Current page controller is re-used on the admin side since we dont want to redirect to the entity url on the front end.
         */
        if (\IPS\Dispatcher::i()->controllerLocation == 'front') {
            $controllerUrl = \IPS\Http\Url::internal("app=rules&module=logs&controller=logviewer&log={$this->id}")
                ->setQueryString(['sortby' => $sortBy, 'sortdirection' => $sortDirection, 'page' => $page]);

            if ($entity) {
                $controllerUrl = $controllerUrl->setQueryString('entity', $entity->activeid);
            }

            if (!static::$tableControllerLoaded) {
                \IPS\Output::i()->jsFiles = array_merge(
                    \IPS\Output::i()->jsFiles,
                    \IPS\Output::i()->js('front_ui.js', 'rules', 'front')
                );
                static::$tableControllerLoaded = true;
            }
        } else {
            $controllerUrl = \IPS\Request::i()->url()->setQueryString(
                ['log' => null, 'logid' => null, 'logdo' => null]
            );
        }

        $entity_where = $entity ? ' AND entity_id=' . $entity->activeid : '';

        if ($entity) {
            $table = new \IPS\rules\Log\Table(
                static::getTableName($this->class),
                $controllerUrl,
                ['log_id=? AND entity_id=?', $this->id, $entity->activeid]
            );
            if ($this->display_time) {
                $table->include[] = 'logtime';
            }
            $table->include[] = 'message';
            $table->noSort = ['message'];
        } else {
            $table = new \IPS\rules\Log\Table(
                static::getTableName($this->class),
                $controllerUrl,
                ['log_id=?', $this->id]
            );
            if ($this->display_time) {
                $table->include[] = 'logtime';
            }
            $table->include[] = 'entity_id';
            $table->include[] = 'message';
            $table->noSort = ['message', 'entity_id'];
        }

        /* If we are setting log params using our proprietary 'logsortby' etc, then the base \IPS\Helpers\Table class overwrites our base url */
        $table->baseUrl = $controllerUrl;

        $table->resortKey = 'listResort_log' . $this->id;
        $table->tableTemplate = [\IPS\Theme::i()->getTemplate('components', 'rules', 'front'), 'logTable'];
        $table->rowsTemplate = [\IPS\Theme::i()->getTemplate('tables', 'core', 'admin'), 'rows'];
        $table->langPrefix = 'rules_custom_logs_table_' . $this->id . '_';
        $table->sortBy = $sortBy;
        $table->sortDirection = $sortDirection;
        $table->page = $page;

        if ($limit) {
            $table->limit = $limit;
        } else {
            $table->limit = $this->limit ?: 25;
        }

        $logObjClass = str_replace('-', '\\', $this->class);

        $table->parsers = [
            'logtime' => function ($val, $row) {
                return (string)\IPS\DateTime::ts($val);
            },
            'entity_id' => function ($val, $row) use ($logObjClass) {
                try {
                    $obj = $logObjClass::load($val);
                    return \IPS\rules\Data::dataDisplayValue($obj);
                } catch (\Exception $e) {
                }

                return $val;
            },
        ];

        foreach ($this->children() as $data) {
            if ($data->can('view')) {
                $lang->words['rules_custom_logs_table_' . $this->id . '_data_' . $data->varname] = $data->name;
                $table->include[] = 'data_' . $data->varname;
                $table->parsers['data_' . $data->varname] = function ($val, $row) use ($data) {
                    if ($data->type == 'object' and \IPS\rules\Data::isConcreteRecord($data->class)) {
                        $objClass = str_replace('-', '\\', $data->class);
                        try {
                            $obj = $objClass::load($val);
                            return \IPS\rules\Data::dataDisplayValue($obj);
                        } catch (\OutOfRangeException $e) {
                            return null;
                        }
                    } else {
                        if ($data->type == 'object') {
                            $obj = \IPS\rules\Application::restoreArg(json_decode($val, true));
                            return \IPS\rules\Data::dataDisplayValue($obj);
                        } else {
                            if ($data->type == 'array') {
                                $array = \IPS\rules\Application::restoreArg(json_decode($val, true));
                                return \IPS\rules\Data::dataDisplayValue($array);
                            }
                        }
                    }

                    return $val;
                };

                if (!in_array($data->type, ['int', 'float', 'string'])) {
                    $table->noSort[] = 'data_' . $data->varname;
                }
            }
        }

        $table->rowButtons = function ($row) use ($controllerUrl, $self) {
            $buttons = [];

            if ($self->can('delete')) {
                $buttons['delete'] = [
                    'icon' => 'trash',
                    'title' => 'delete',
                    'id' => "{$row['id']}-delete",
                    'link' => $controllerUrl->setQueryString(
                        ['log' => $self->id, 'logdo' => 'delete', 'logid' => $row['id']]
                    ),
                    'data' => \IPS\Dispatcher::i(
                    )->controllerLocation == 'admin' ? ['delete' => ''] : ['confirm' => ''],
                ];
            }

            return $buttons;
        };

        return $table;
    }

    /**
     * Get a tabbed output of all logs for an entity
     *
     * @param object $entity The entity to count logs for
     * @param int $limit The number of logs to show per page
     * @param
     */
    public static function allLogs($entity, $limit = null)
    {
        $output = [];
        $activeTab = \IPS\Request::i()->logtab ? 'custom_log_' . \IPS\Request::i()->logtab : null;
        $logs = 0;

        foreach (
            \IPS\rules\Log\Custom::roots(
                'view',
                null,
                [['custom_log_class=? AND custom_log_enabled=1', $entity::rulesDataClass()]]
            ) as $log
        ) {
            if ($log->display_empty or $log->logCount($entity)) {
                $tab_title = 'custom_log_' . $log->id;
                $activeTab = $activeTab ?: $tab_title;
                \IPS\Member::loggedIn()->language()->words['custom_log_' . $log->id] = $log->title;

                $output[$tab_title] = (string)$log->logsTable($entity, $limit);
                $logs++;
            }
        }

        return $logs ? \IPS\Theme::i()->getTemplate('components', 'rules', 'front')->logsTables(
            $output,
            $activeTab,
            $entity->activeid
        ) : null;
    }

}