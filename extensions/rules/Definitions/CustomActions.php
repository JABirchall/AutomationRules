<?php
/**
 * @brief        Rules extension: CustomActions
 * @package        Rules for IPS Social Suite
 * @since        04 Mar 2015
 * @version        SVN_VERSION_NUMBER
 */

namespace IPS\rules\extensions\rules\Definitions;

/* To prevent PHP errors (extending class does not exist) revealing path */
if (!defined('\IPS\SUITE_UNIQUE_KEY')) {
    header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}

/**
 * @brief    Rules definitions extension: CustomActions
 */
class _CustomActions
{

    /**
     * @brief    Group events and actions in this extension with other extensions by group name
     */
    public $defaultGroup = 'Custom Actions';

    /**
     * Triggerable Events
     *
     * Define the events that can be triggered by your application
     *
     * @return    array        Array of event definitions
     */
    public function events()
    {
        $lang = \IPS\Member::loggedIn()->language();
        $events = [];

        foreach (\IPS\rules\Action\Custom::roots() as $action) {
            $arguments = [];
            foreach ($action->children() as $argument) {
                $argClass = null;
                if ($argument->type == 'object') {
                    $argClass = $argument->class == 'custom' ? $argument->custom_class : str_replace(
                        '-',
                        '\\',
                        $argument->class
                    );
                }

                $lang->words['rules_CustomActions_event_custom_action_' . $action->key . '_' . $argument->varname] = $argument->description;
                $arguments[$argument->varname] = [
                    'argtype' => $argument->type,
                    'class' => $argClass ?: null,
                    'nullable' => !$argument->required,
                ];
            }

            $lang->words['rules_CustomActions_event_custom_action_' . $action->key] = 'Action triggered: ' . $action->title;
            $events['custom_action_' . $action->key] = [
                'arguments' => $arguments,
            ];
        }

        return $events;
    }

    /**
     * Conditional Operations
     *
     * You can define your own conditional operations which can be
     * added to rules as conditions.
     *
     * @return    array        Array of conditions definitions
     */
    public function conditions()
    {
        $conditions = [];

        return $conditions;
    }

    /**
     * Triggerable Actions
     *
     * @return    array        Array of action definitions
     */
    public function actions()
    {
        $lang = \IPS\Member::loggedIn()->language();
        $actions = [];

        foreach (\IPS\rules\Action\Custom::roots() as $action) {
            $arguments = [];
            foreach ($action->children() as $argument) {
                $argClass = null;
                if ($argument->type == 'object') {
                    $argClass = $argument->class == 'custom' ? $argument->custom_class : str_replace(
                        '-',
                        '\\',
                        $argument->class
                    );
                }

                $lang->words['rules_CustomActions_actions_custom_action_' . $action->key . '_' . $argument->varname] = $argument->name;
                $arguments[$argument->varname] = [
                    'argtypes' => [
                        $argument->type => [
                            'description' => $argument->description,
                            'class' => $argClass ?: null,
                        ],
                    ],
                    'required' => (bool)$argument->required,
                ];
            }

            $lang->words['rules_CustomActions_actions_custom_action_' . $action->key] = $action->title;
            $actions['custom_action_' . $action->key] = [
                'callback' => function () use ($action) {
                    $event = \IPS\rules\Event::load('rules', 'CustomActions', 'custom_action_' . $action->key);
                    call_user_func_array([$event, 'trigger'], func_get_args());
                    return "custom action triggered (ID:#{$action->id})";
                },
                'arguments' => $arguments,
            ];
        }

        return $actions;
    }

}