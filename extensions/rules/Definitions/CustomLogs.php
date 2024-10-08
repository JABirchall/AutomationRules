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
class _CustomLogs
{

    /**
     * @brief    Group events and actions in this extension with other extensions by group name
     */
    public $defaultGroup = 'Custom Logs';

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

        foreach (\IPS\rules\Log\Custom::roots(null) as $log) {
            $arguments = [
                'entity' => [
                    'argtype' => 'object',
                    'class' => str_replace('-', '\\', $log->class),
                    'nullable' => false,
                ],
                'message' => [
                    'argtype' => 'string',
                    'nullable' => false,
                ],
            ];
            $lang->words['rules_CustomLogs_event_custom_log_' . $log->key . '_entity'] = $lang->get(
                'rules_custom_log_entity'
            );
            $lang->words['rules_CustomLogs_event_custom_log_' . $log->key . '_message'] = $lang->get(
                'rules_custom_log_message'
            );

            foreach ($log->children(null) as $argument) {
                $argClass = null;
                if ($argument->type == 'object') {
                    $argClass = $argument->class == 'custom' ? $argument->custom_class : str_replace(
                        '-',
                        '\\',
                        $argument->class
                    );
                }

                $lang->words['rules_CustomLogs_event_custom_log_' . $log->key . '_' . $argument->varname] = $argument->description;
                $arguments[$argument->varname] = [
                    'argtype' => $argument->type,
                    'class' => $argClass ?: null,
                    'nullable' => !$argument->required,
                ];
            }

            $lang->words['rules_CustomLogs_event_custom_log_' . $log->key] = 'Entry Logged: ' . $log->title;
            $events['custom_log_' . $log->key] = [
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

        foreach (\IPS\rules\Log\Custom::roots(null) as $log) {
            $entityConfig = null;
            $objectClass = str_replace('-', '\\', $log->class);

            if ($objectClass == '\IPS\Member') {
                $entityConfig = \IPS\rules\Application::configPreset('member', 'rules_choose_member');
            } else {
                if (is_subclass_of($objectClass, '\IPS\Node\Model')) {
                    $entityConfig = \IPS\rules\Application::configPreset(
                        'node',
                        $objectClass::$nodeTitle,
                        true,
                        ['class' => $objectClass]
                    );
                } else {
                    if (is_subclass_of($objectClass, '\IPS\Content\Item')) {
                        $entityConfig = \IPS\rules\Application::configPreset(
                            'item',
                            $objectClass::$title,
                            true,
                            ['class' => $objectClass]
                        );
                    }
                }
            }

            $arguments = [
                'entity' => [
                    'argtypes' => [
                        'object' => [
                            'class' => $objectClass,
                            'description' => $objectClass . ' object',
                        ],
                    ],
                    'required' => true,
                    'configuration' => $entityConfig,
                ],
                'message' => [
                    'argtypes' => ['string'],
                    'required' => true,
                    'configuration' => [
                        'form' => function ($form, $values) {
                            $form->add(
                                new \IPS\Helpers\Form\Text(
                                    'rules_custom_log_message',
                                    isset($values['rules_custom_log_message']) ? $values['rules_custom_log_message'] : '',
                                    true,
                                    [],
                                    null,
                                    null,
                                    null,
                                    'rules_custom_log_message'
                                )
                            );
                            return ['rules_custom_log_message'];
                        },
                        'getArg' => function ($values) {
                            return isset($values['rules_custom_log_message']) ? $values['rules_custom_log_message'] : '';
                        },
                    ],
                ],
            ];
            $lang->words['rules_CustomLogs_actions_custom_log_' . $log->key . '_entity'] = $lang->get(
                'rules_custom_log_entity'
            );
            $lang->words['rules_CustomLogs_actions_custom_log_' . $log->key . '_message'] = $lang->get(
                'rules_custom_log_message'
            );

            $logData = [];
            foreach ($log->children(null) as $argument) {
                $logData[$argument->varname] = null;
                $argClass = null;
                if ($argument->type == 'object') {
                    $argClass = $argument->class == 'custom' ? $argument->custom_class : str_replace(
                        '-',
                        '\\',
                        $argument->class
                    );
                }

                $lang->words['rules_CustomLogs_actions_custom_log_' . $log->key . '_' . $argument->varname] = $argument->name;
                $arguments[$argument->varname] = [
                    'argtypes' => [
                        $argument->type => [
                            'description' => $argument->description,
                            'class' => $argClass ?: null,
                        ],
                    ],
                    'required' => (bool)$argument->required,
                    'configuration' => [
                        'form' => function ($form, $values) use ($argument, $lang) {
                            $form_name = 'custom_argument_' . $argument->id;
                            $form_value = isset($values['custom_argument_' . $argument->id]) ? \IPS\rules\Application::restoreArg(
                                json_decode($values['custom_argument_' . $argument->id], true)
                            ) : null;
                            $form_input = null;

                            $lang->words[$form_name] = $argument->name;
                            $lang->words[$form_name . '_desc'] = $argument->description;

                            switch ($argument->type) {
                                case 'int':
                                    $form_input = new \IPS\Helpers\Form\Number(
                                        $form_name,
                                        $form_value,
                                        $argument->required,
                                        ['min' => null],
                                        null,
                                        null,
                                        null,
                                        $form_name
                                    );
                                    break;
                                case 'float':
                                    $form_input = new \IPS\Helpers\Form\Number(
                                        $form_name,
                                        $form_value,
                                        $argument->required,
                                        ['min' => null, 'decimals' => true],
                                        null,
                                        null,
                                        null,
                                        $form_name
                                    );
                                    break;
                                case 'string':
                                    $form_input = new \IPS\Helpers\Form\TextArea(
                                        $form_name,
                                        $form_value,
                                        $argument->required,
                                        [],
                                        null,
                                        null,
                                        null,
                                        $form_name
                                    );
                                    break;
                                case 'bool':
                                    $form_input = new \IPS\Helpers\Form\YesNo(
                                        $form_name,
                                        $form_value,
                                        $argument->required,
                                        [],
                                        null,
                                        null,
                                        null,
                                        $form_name
                                    );
                                    break;
                                case 'object':

                                    $objectClass = $argument->class == 'custom' ? $argument->custom_class : str_replace(
                                        '-',
                                        '\\',
                                        $argument->class
                                    );

                                    /* Node Select */
                                    if (is_subclass_of($objectClass, '\IPS\Node\Model')) {
                                        $form_input = new \IPS\Helpers\Form\Node(
                                            $form_name,
                                            $form_value,
                                            $argument->required,
                                            [
                                                'class' => $objectClass,
                                                'multiple' => false,
                                                'permissionCheck' => 'view',
                                            ],
                                            null,
                                            null,
                                            null,
                                            $form_name
                                        );
                                        $bulk_options[$form_name] = $argument->name;
                                    } /* Content Select */
                                    else {
                                        if (is_subclass_of($objectClass, '\IPS\Content\Item')) {
                                            $form_input = new \IPS\rules\Field\Content(
                                                $form_name,
                                                $form_value,
                                                $argument->required,
                                                ['multiple' => 1, 'class' => $objectClass],
                                                null,
                                                null,
                                                null,
                                                $form_name
                                            );
                                            $bulk_options[$form_name] = $argument->name;
                                        } /* Member Select */
                                        else {
                                            if ($objectClass == '\IPS\Member') {
                                                $form_input = new \IPS\Helpers\Form\Member(
                                                    $form_name,
                                                    $form_value,
                                                    $argument->required,
                                                    ['multiple' => 1],
                                                    null,
                                                    null,
                                                    null,
                                                    $form_name
                                                );
                                                $bulk_options[$form_name] = $argument->name;
                                            } /* Date Select */
                                            else {
                                                if ($objectClass == '\IPS\DateTime') {
                                                    $form_input = new \IPS\Helpers\Form\Date(
                                                        $form_name,
                                                        $form_value,
                                                        $argument->required,
                                                        ['time' => true],
                                                        null,
                                                        null,
                                                        null,
                                                        $form_name
                                                    );
                                                } /* Url Input */
                                                else {
                                                    if ($objectClass == '\IPS\Http\Url') {
                                                        $form_input = new \IPS\Helpers\Form\Url(
                                                            $form_name,
                                                            $form_value,
                                                            $argument->required,
                                                            [],
                                                            null,
                                                            null,
                                                            null,
                                                            $form_name
                                                        );
                                                    }
                                                }
                                            }
                                        }
                                    }

                                    break;

                                case 'array':

                                    $objectClass = $argument->class == 'custom' ? $argument->custom_class : str_replace(
                                        '-',
                                        '\\',
                                        $argument->class
                                    );

                                    /* Multiple Node Select */
                                    if (is_subclass_of($objectClass, '\IPS\Node\Model')) {
                                        $form_input = new \IPS\Helpers\Form\Node(
                                            $form_name,
                                            $form_value,
                                            $argument->required,
                                            [
                                                'class' => $objectClass,
                                                'multiple' => true,
                                                'permissionCheck' => 'view',
                                            ],
                                            null,
                                            null,
                                            null,
                                            $form_name
                                        );
                                    } /* Multiple Content Select */
                                    else {
                                        if (is_subclass_of($objectClass, '\IPS\Content\Item')) {
                                            $form_input = new \IPS\rules\Field\Content(
                                                $form_name,
                                                $form_value,
                                                $argument->required,
                                                ['multiple' => null, 'class' => $objectClass],
                                                null,
                                                null,
                                                null,
                                                $form_name
                                            );
                                        } /* Multiple Member Select */
                                        else {
                                            if ($objectClass == '\IPS\Member') {
                                                $form_input = new \IPS\Helpers\Form\Member(
                                                    $form_name,
                                                    $form_value,
                                                    $argument->required,
                                                    ['multiple' => null],
                                                    null,
                                                    null,
                                                    null,
                                                    $form_name
                                                );
                                            } /* Multiple Date Select */
                                            else {
                                                if ($objectClass == '\IPS\DateTime') {
                                                    $form_input = new \IPS\Helpers\Form\Stack(
                                                        $form_name,
                                                        $form_value,
                                                        $argument->required,
                                                        ['stackFieldType' => 'Date', 'time' => false],
                                                        null,
                                                        null,
                                                        null,
                                                        $form_name
                                                    );
                                                } /* Multiple Urls */
                                                else {
                                                    if ($objectClass == '\IPS\Http\Url') {
                                                        $form_input = new \IPS\Helpers\Form\Stack(
                                                            $form_name,
                                                            $form_value,
                                                            $argument->required,
                                                            ['stackFieldType' => 'Url'],
                                                            null,
                                                            null,
                                                            null,
                                                            $form_name
                                                        );
                                                    } /* Multiple Arbitrary Values */
                                                    else {
                                                        if ($objectClass == '') {
                                                            $form_input = new \IPS\Helpers\Form\Stack(
                                                                $form_name,
                                                                $form_value,
                                                                $argument->required,
                                                                [],
                                                                null,
                                                                null,
                                                                null,
                                                                $form_name
                                                            );
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }

                                    break;
                            }

                            if ($form_input) {
                                $form->add($form_input);
                                return [$form_name];
                            }
                        },
                        'saveValues' => function (&$values) use ($argument) {
                            $form_name = 'custom_argument_' . $argument->id;
                            $values[$form_name] = json_encode(\IPS\rules\Application::storeArg($values[$form_name]));
                        },
                        'getArg' => function ($values) use ($argument) {
                            $form_name = 'custom_argument_' . $argument->id;
                            return \IPS\rules\Application::restoreArg(json_decode($values[$form_name], true));
                        },
                    ],
                ];
            }

            $lang->words['rules_CustomLogs_actions_custom_log_' . $log->key] = 'Create log entry: ' . $log->title;
            $actions['custom_log_' . $log->key] = [
                'callback' => function () use ($log, $logData) {
                    $args = func_get_args();

                    /* Get required log arguments */
                    $entity = array_shift($args);
                    $message = array_shift($args);

                    /* Shift extra arguments into log data array */
                    foreach ($logData as $key => $value) {
                        $logData[$key] = array_shift($args);
                    }

                    /* Create the entry */
                    if ($log->createEntry($entity, $message, $logData)) {
                        /* Trigger other rules */
                        $event = \IPS\rules\Event::load('rules', 'CustomLogs', 'custom_log_' . $log->key);
                        call_user_func_array([$event, 'trigger'], func_get_args());

                        return "Entry logged to: {$log->title}";
                    } else {
                        return "Entry not logged (log disabled)";
                    }
                },
                'arguments' => $arguments,
            ];
        }

        return $actions;
    }

}