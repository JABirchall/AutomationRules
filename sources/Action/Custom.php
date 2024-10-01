<?php
/**
 * @brief        IPS4 Rules
 * @author        Kevin Carwile (http://www.linkedin.com/in/kevincarwile)
 * @copyright        (c) 2014 - Kevin Carwile
 * @package        Rules
 * @since        6 Feb 2015
 */


namespace IPS\rules\Action;

/* To prevent PHP errors (extending class does not exist) revealing path */
if (!defined('\IPS\SUITE_UNIQUE_KEY')) {
    header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}

/**
 * Node
 */
class _Custom extends \IPS\Node\Model
{
    /**
     * @brief    [ActiveRecord] Multiton Store
     */
    protected static $multitons;

    /**
     * @brief    [ActiveRecord] Database Table
     */
    public static $databaseTable = 'rules_custom_actions';

    /**
     * @brief    [ActiveRecord] Database Prefix
     */
    public static $databasePrefix = 'custom_action_';

    /**
     * @brief    [Node] Order Database Column
     */
    public static $databaseColumnOrder = 'weight';

    /**
     * @brief    [ActiveRecord] Database ID Fields
     */
    protected static $databaseIdFields = ['custom_action_key'];

    /**
     * @brief    [Node] Parent ID Database Column
     */
    public static $databaseColumnParent = null;

    /**
     * @brief    Sub Node Class
     */
    public static $subnodeClass = '\IPS\rules\Action\Argument';

    /**
     * @brief    [Node] Node Title
     */
    public static $nodeTitle = 'custom_actions';

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
        return null;
    }

    /**
     * [Node] Set whether or not this node is enabled
     *
     * @param bool|int $enabled Whether to set it enabled or disabled
     * @return    void
     */
    protected function set__enabled($enabled)
    {
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
     * Trigger this rule with arguments
     *
     * @param array $args A keyed array of arguments
     * @return    void
     */
    public function trigger($args)
    {
        $arguments = [];
        foreach ($this->children() as $argument) {
            if (isset($args[$argument->varname])) {
                $arguments[] = $args[$argument->varname];
            } else {
                $arguments[] = null;
            }
        }

        call_user_func_array([$this->event(), 'trigger'], $arguments);
    }

    /**
     * Get Event
     *
     * @return    \IPS\rules\Event
     */
    public function event()
    {
        return \IPS\rules\Event::load('rules', 'CustomActions', 'custom_action_' . $this->key);
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

        $buttons['schedule'] = [
            'icon' => 'clock-o',
            'title' => 'rules_schedule_custom_now',
            'link' => \IPS\Http\Url::internal(
                "app=rules&module=rules&controller=schedule&do=newSchedule"
            )->setQueryString(['custom_id' => $this->id]),
            'data' => [],
        ];

        return $buttons;
    }

    /**
     * [Node] Custom Badge
     *
     * @return    NULL|array    Null for no badge, or an array of badge data (0 => CSS class type, 1 => language string, 2 => optional raw HTML to show instead of language string)
     */
    protected function get__badge()
    {
        if (0) {
            return [
                0 => 'ipsBadge ipsBadge_intermediary',
                1 => 'badge text',
            ];
        }

        return null;
    }

    /**
     * [Node] Add/Edit Form
     *
     * @param \IPS\Helpers\Form $form The form
     * @return    void
     */
    public function form(&$form)
    {
        $self = $this;

        $form->add(new \IPS\Helpers\Form\Text('custom_action_title', $this->title, true));
        $form->add(new \IPS\Helpers\Form\TextArea('custom_action_description', $this->description, false));
        $form->add(
            new \IPS\Helpers\Form\YesNo(
                'custom_action_enable_api',
                $this->enable_api,
                false,
                ['togglesOn' => ['custom_action_api_methods']],
                null,
                null,
                null,
                'custom_action_enable_api'
            )
        );
        $form->add(
            new \IPS\Helpers\Form\CheckboxSet(
                'custom_action_api_methods',
                $this->api_methods ? explode(',', $this->api_methods) : [],
                true,
                ['options' => ['GET' => 'GET', 'POST' => 'POST', 'PUT' => 'PUT', 'DELETE' => 'DELETE']],
                null,
                null,
                null,
                'custom_action_api_methods'
            )
        );

        parent::form($form);
    }

    /**
     * Recursion Protection
     */
    public $locked = false;

    /**
     * [Node] Save Add/Edit Form
     *
     * @param array $values Values from the form
     * @return    void
     */
    public function saveForm($values)
    {
        $values['custom_action_api_methods'] = implode(',', $values['custom_action_api_methods']);
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

        parent::save();
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
     * [ActiveRecord] Delete Record
     *
     * @return    void
     */
    public function delete()
    {
        foreach ($this->children() as $argument) {
            $argument->delete();
        }

        return parent::delete();
    }

}