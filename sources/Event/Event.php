<?php
/**
 * @brief        IPS4 Rules
 * @author        Kevin Carwile (http://www.linkedin.com/in/kevincarwile)
 * @copyright        (c) 2014 - Kevin Carwile
 * @package        Rules
 * @since        6 Feb 2015
 */


namespace IPS\rules;

/* To prevent PHP errors (extending class does not exist) revealing path */
if (!defined('\IPS\SUITE_UNIQUE_KEY')) {
    header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}

/**
 * Node
 */
class _Event
{

    /**
     * @brief    App
     */
    public $app = null;

    /**
     * @brief    Class
     */
    public $class = null;

    /**
     * @brief    Action Key
     */
    public $key = null;

    /**
     * @brief    Event Data
     */
    public $data = null;

    /**
     * @brief    Deferred Action Stack
     */
    public $actionStack = [];

    /**
     * Multiton Cache
     */
    public static $multitons = [];

    /**
     * Placeholder Flag
     */
    public $placeholder = false;

    /**
     * API Response Params
     */
    public $apiResponse = [];

    /**
     * Event Loader
     *
     * @param string $app App that defines the action
     * @param string $class Extension class where action is defined
     * @param string $key Action key
     * @param bool $forced Load event regardless of if there are any rules attached to it
     * @return    \IPS\rules\Event    Return a rules event object
     */
    public static function load($app = 'null', $class = 'null', $key = 'null', $forced = false)
    {
        if (isset (static::$multitons[$app][$class][$key])) {
            return static::$multitons[$app][$class][$key];
        }

        if ($forced or static::hasRules($app, $class, $key)) {
            try {
                return static::$multitons[$app][$class][$key] = new \IPS\rules\Event($app, $class, $key);
            } catch (\BadMethodCallException $e) {
                /* Return a placeholder event */
                return static::$multitons[$app][$class][$key] = new \IPS\rules\Event\Placeholder($app, $class, $key);
            }
        } else {
            /* Return a placeholder event */
            return new \IPS\rules\Event\Placeholder($app, $class, $key, false);
        }
    }

    /**
     * Events Cache
     */
    protected static $eventsCache = [];

    /**
     * Constructor
     *
     * @param string $app App that defines the action
     * @param string $class Extension class where action is defined
     * @param string $key Action key
     */
    public function __construct($app, $class, $key)
    {
        $this->app = $app;
        $this->class = $class;
        $this->key = $key;

        $extClass = '\IPS\\' . $app . '\extensions\rules\Definitions\\' . $class;
        if (class_exists($extClass)) {
            if (isset(static::$eventsCache[$app][$class])) {
                $events = static::$eventsCache[$app][$class];
            } else {
                $ext = new $extClass;
                $events = static::$eventsCache[$app][$class] = method_exists($ext, 'events') ? $ext->events() : [];
            }

            if (isset ($events[$key])) {
                $this->data = $events[$key];
                static::$multitons[$this->app][$this->class][$this->key] = $this;
            } else {
                throw new \BadMethodCallException(\IPS\Member::loggedIn()->language()->get('rules_event_not_found'));
            }
        } else {
            throw new \BadMethodCallException(\IPS\Member::loggedIn()->language()->get('rules_event_not_found'));
        }
    }

    /**
     * Root Thread ID
     *
     * This is the thread for which deferred actions should be executed
     */
    public $rootThread = null;

    /**
     * Thread ID
     */
    public $thread = null;

    /**
     * Parent Thread ID
     */
    public $parentThread = null;

    /**
     * Recursion Protection
     */
    public $locked = false;

    /**
     * Trigger An Event
     */
    public function trigger()
    {
        if (!$this->locked) {
            /* Don't do this during an upgrade */
            if (\IPS\Dispatcher::hasInstance() and \IPS\Dispatcher::i()->controllerLocation === 'setup') {
                return;
            }

            /**
             * Give each new event triggered a unique thread id so
             * logs can be tied back to the event that generated them
             */
            $parentThread = $this->parentThread;
            $this->parentThread = $this->thread;
            $this->thread = md5(uniqid() . mt_rand());

            foreach ($this->rules() as $rule) {
                if (!$rule->ruleset() or $rule->ruleset()->enabled) {
                    if ($rule->enabled) {
                        $result = call_user_func_array([$rule, 'invoke'], func_get_args());

                        if ($rule->debug) {
                            \IPS\rules\Application::rulesLog($this, $rule, null, $result, 'Rule evaluated');
                        }
                    } else {
                        if ($rule->debug) {
                            \IPS\rules\Application::rulesLog($this, $rule, null, '--', 'Rule not evaluated (disabled)');
                        }
                    }
                } else {
                    if ($rule->debug) {
                        \IPS\rules\Application::rulesLog(
                            $this,
                            $rule,
                            null,
                            '--',
                            'Rule not evaluated (rule set disabled)'
                        );
                    }
                }
            }

            $this->thread = $this->parentThread;
            $this->parentThread = $parentThread;

            /**
             * Deferred Actions
             *
             * Only execute deferred actions at the root thread level
             */
            if ($this->thread === $this->rootThread) {
                $actions = $this->actionStack;
                $this->actionStack = [];
                $this->executeDeferred($actions);
            }
        }
    }

    /**
     * Execute Deferred
     *
     * @param array $actions Deferred actions to execute
     * @return    void
     */
    public function executeDeferred($actions)
    {
        $this->locked = true;

        while ($deferred = array_shift($actions)) {
            $action = $deferred['action'];
            $this->thread = isset($deferred['thread']) ? $deferred['thread'] : null;
            $this->parentThread = isset($deferred['parentThread']) ? $deferred['parentThread'] : null;

            /**
             * Execute the action
             */
            try {
                $action->locked = true;

                $result = call_user_func_array(
                    $action->definition['callback'],
                    array_merge(
                        $deferred['args'],
                        [$action->data['configuration']['data'], $deferred['event_args'], $action]
                    )
                );

                $action->locked = false;

                if ($rule = $action->rule() and $rule->debug) {
                    \IPS\rules\Application::rulesLog($this, $rule, $action, $result, 'Evaluated');
                }
            } catch (\Exception $e) {
                /**
                 * Log Exceptions
                 */
                $paths = explode('/', str_replace('\\', '/', $e->getFile()));
                $file = array_pop($paths);
                \IPS\rules\Application::rulesLog(
                    $this,
                    $action->rule(),
                    $action,
                    $e->getMessage() . '<br>Line: ' . $e->getLine() . ' of ' . $file,
                    'Operation Callback Exception',
                    1
                );
            }
        }

        $this->locked = false;

        /* Reset threads */
        $this->thread = $this->parentThread = $this->rootThread = null;
    }

    /**
     * Get Event Title
     */
    public function title()
    {
        $lang = \IPS\Member::loggedIn()->language();

        if ($lang->checkKeyExists($this->app . '_' . $this->class . '_event_' . $this->key)) {
            return $lang->get($this->app . '_' . $this->class . '_event_' . $this->key);
        }

        return 'Untitled ( ' . $this->app . ' / ' . $this->class . ' / ' . $this->key . ' )';
    }

    /**
     * @brief    Cache for rules
     */
    protected $rulesCache = null;

    /**
     * Get rules attached to this event
     */
    public function rules()
    {
        if (isset($this->rulesCache)) {
            return $this->rulesCache;
        }

        try {
            return $this->rulesCache = \IPS\rules\Rule::roots(
                null,
                null,
                [
                    [
                        'rule_event_app=? AND rule_event_class=? AND rule_event_key=?',
                        $this->app,
                        $this->class,
                        $this->key,
                    ],
                ]
            );
        } catch (\Exception $e) {
            /* Uninstalled */
            return $this->rulesCache = [];
        }
    }

    /* hasRules Cache */
    public static $hasRules = [];

    /**
     * Check if rules are attached to an event
     *
     * @param string $app App that defines the action
     * @param string $class Extension class where action is defined
     * @param string $key Action key
     * @param bool $enabled Whether to only count enabled rules
     * @return    bool
     */
    public static function hasRules($app, $class, $key, $enabled = true)
    {
        if (isset(static::$hasRules[$app][$class][$key][(int)$enabled])) {
            return static::$hasRules[$app][$class][$key][(int)$enabled];
        }

        try {
            return static::$hasRules[$app][$class][$key][(int)$enabled] = (bool)\IPS\rules\Rule::roots(
                null,
                null,
                [
                    [
                        'rule_event_app=? AND rule_event_class=? AND rule_event_key=? AND rule_enabled=1',
                        $app,
                        $class,
                        $key,
                    ],
                ]
            );
        } catch (\Exception $e) {
            /* Uninstalled */
            return static::$hasRules[$app][$class][$key][(int)$enabled] = false;
        }
    }

}