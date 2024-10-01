<?php


namespace IPS\rules\modules\admin\rules;

/* To prevent PHP errors (extending class does not exist) revealing path */
if (!defined('\IPS\SUITE_UNIQUE_KEY')) {
    header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}

/**
 * logs
 */
class _logs extends \IPS\Dispatcher\Controller
{
    /**
     * Execute
     *
     * @return    void
     */
    public function execute()
    {
        \IPS\Dispatcher::i()->checkAcpPermission('logs_manage');
        parent::execute();
    }

    /**
     * Manage
     *
     * @return    void
     */
    protected function manage()
    {
        \IPS\Output::i()->sidebar['actions']['flush'] = [
            'icon' => 'trash',
            'link' => \IPS\Http\Url::internal('app=rules&module=rules&controller=logs&do=flushlogs'),
            'title' => 'rules_flush_logs',
            'data' => [
                'confirm' => '',
                'confirmMessage' => 'This will delete system logs only. All other logs will remain.',
            ],
        ];

        \IPS\Output::i()->sidebar['actions']['prune'] = [
            'icon' => 'cut',
            'link' => \IPS\Http\Url::internal('app=rules&module=rules&controller=logs&do=prunelogs'),
            'title' => 'rules_prune_logs',
            'data' => [
                'confirm' => '',
                'confirmMessage' => 'This will prune all custom logs according to their log settings.',
            ],
        ];

        $tab = \IPS\Request::i()->tab ?: 'system';
        $tabs = ['system' => \IPS\Member::loggedIn()->language()->addToStack('rules_system_log')];

        foreach (\IPS\rules\Log\Custom::roots() as $log) {
            $tabs['log_' . $log->id] = $log->title;
            if (\IPS\Request::i()->tab == 'log_' . $log->id) {
                \IPS\Request::i()->log = $log->id;
                $table = $log->logsTable(null, 25);
            }
        }

        if ($tab == 'system' or !isset($table)) {
            $table = $this->_systemLogsTable();
        }

        if (\IPS\Request::i()->isAjax()) {
            \IPS\Output::i()->output = "<div class='ipsPad'>{$table}</div>";
        } else {
            \IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack('rules_logs');
            \IPS\Output::i()->output = \IPS\Theme::i()->getTemplate('global', 'core')->block(
                'title',
                \IPS\Theme::i()->getTemplate('global', 'core')->tabs(
                    $tabs,
                    $tab,
                    "<div class='ipsPad'>{$table}</div>",
                    \IPS\Request::i()->url(),
                    'tab',
                    false,
                    true
                )
            );
        }
    }

    /**
     * Get system logs table
     */
    protected function _systemLogsTable()
    {
        /* Create the table */
        $controllerUrl = \IPS\Http\Url::internal("app=rules&module=rules&controller=rulesets&do=viewlog");
        $table = new \IPS\Helpers\Table\Db(
            'rules_logs',
            \IPS\Http\Url::internal('app=rules&module=rules&controller=logs'),
            ['error>0 OR ( op_id=0 AND rule_parent=0 )']
        );
        $table->include = ['type', 'app', 'key', 'message', 'result', 'time'];
        $table->langPrefix = 'rules_logs_table_';
        $table->parsers = [
            'app' => function ($val, $row) {
                $event = \IPS\rules\Event::load($row['app'], $row['class'], $row['key'], true);
                return $event->title();
            },
            'key' => function ($val, $row) {
                if ($row['rule_id']) {
                    try {
                        $rule = \IPS\rules\Rule::load($row['rule_id']);
                        return "<a href='" . \IPS\Http\Url::internal(
                                "app=rules&module=rules&controller=rules&id={$rule->id}&do=form"
                            ) . "'>{$rule->title}</a>";
                    } catch (\OutOfRangeException $e) {
                    }
                }
            },
            'time' => function ($val) {
                return (string)\IPS\DateTime::ts($val);
            },
            'type' => function ($val, $row) {
                return $row['error'] ? "<span style='color:red'><i class='fa fa-warning'></i> Error Log</span>" : "<i class='fa fa-bug'></i> Rule Debug Log";
            },
            'result' => function ($val) {
                return json_decode($val);
            },
        ];
        $table->sortBy = \IPS\Request::i()->sortby ?: 'id';
        $table->sortDirection = \IPS\Request::i()->sortdirection ?: 'desc';
        $table->rowButtons = function ($row) use ($controllerUrl) {
            $buttons = [];

            if ($row['rule_id']) {
                $buttons['view'] = [
                    'icon' => 'search',
                    'title' => 'View Log Info',
                    'id' => "{$row['id']}-view",
                    'link' => $controllerUrl->setQueryString(['logid' => $row['id']]),
                    'data' => ['ipsDialog' => ''],
                ];
            }

            return $buttons;
        };
        $table->noSort = ['id', 'key', 'type', 'message', 'result'];

        return $table;
    }

    /**
     * Flush the system log
     *
     * @return    void
     */
    protected function flushlogs()
    {
        $db = \IPS\Db::i();
        $db->delete('rules_logs');
        $db->query("ALTER TABLE `{$db->prefix}rules_logs` AUTO_INCREMENT = 1");
        \IPS\Output::i()->redirect($this->url->setQueryString('do', null), 'rules_logs_flushed');
    }

    /**
     * Prune custom logs
     *
     * @return    void
     */
    protected function prunelogs()
    {
        foreach (\IPS\rules\Log\Custom::roots() as $log) {
            $log->prune();
        }

        \IPS\Output::i()->redirect($this->url->setQueryString('do', null), 'rules_logs_pruned');
    }

}