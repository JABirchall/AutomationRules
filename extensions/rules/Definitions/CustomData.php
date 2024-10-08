<?php
/**
 * @brief        Rules extension: CustomData
 * @package        Rules for IPS Social Suite
 * @since        22 Mar 2015
 * @version        SVN_VERSION_NUMBER
 */

namespace IPS\rules\extensions\rules\Definitions;

/* To prevent PHP errors (extending class does not exist) revealing path */
if (!defined('\IPS\SUITE_UNIQUE_KEY')) {
    header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}

/**
 * @brief    Rules definitions extension: CustomData
 */
class _CustomData
{

    /**
     * @brief    The default option group title to list events, conditions, and actions from this class
     */
    public $defaultGroup = 'Custom Data';

    /**
     * Triggerable Events
     *
     * Define the events that can be triggered by your application
     *
     * @return    array        Array of event definitions
     */
    public function events()
    {
        $events = [];
        $lang = \IPS\Member::loggedIn()->language();

        foreach (\IPS\rules\Data::roots(null) as $data_field) {
            $event_id = 'updated_' . $data_field->key;
            $data_class = in_array($data_field->type, ['object', 'array']) ? str_replace(
                '-',
                '\\',
                $data_field->type_class
            ) : null;

            $lang->words['rules_CustomData_event_' . $event_id] = 'Custom data updated: ' . $data_field->name;
            $lang->words['rules_CustomData_event_' . $event_id . '_entity'] = $data_field->entityTitle();
            $lang->words['rules_CustomData_event_' . $event_id . '_value'] = 'The updated ' . mb_strtolower(
                    $data_field->name
                ) . ' value';

            $events[$event_id] = [
                'arguments' => [
                    'entity' => [
                        'argtype' => 'object',
                        'class' => str_replace('-', '\\', $data_field->class),
                    ],
                    'value' => [
                        'argtype' => $data_field->type,
                        'class' => $data_class,
                    ],
                ],
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
        $actions = [];
        $lang = \IPS\Member::loggedIn()->language();

        foreach (\IPS\rules\Data::roots(null) as $data_field) {
            $action_id = 'set_' . $data_field->key;
            $data_class = in_array($data_field->type, ['object', 'array']) ? str_replace(
                '-',
                '\\',
                $data_field->type_class
            ) : null;

            $lang->words['rules_CustomData_actions_' . $action_id] = 'Update custom data: ' . $data_field->name;
            $lang->words['rules_CustomData_actions_' . $action_id . '_entity'] = $data_field->entityTitle();
            $lang->words['rules_CustomData_actions_' . $action_id . '_value'] = 'The Update Value';

            $actions[$action_id] = [
                'configuration' => [
                    'form' => function ($form, $values) use ($data_field) {
                        switch ($data_field->type) {
                            case 'int':
                            case 'float':

                                $numeric_options = [
                                    'add' => 'Add to the existing value',
                                    'subtract' => 'Subtract from the existing value',
                                    'multiply' => 'Multiply the existing value',
                                    'divide' => 'Divide the existing value',
                                    'set' => 'Explicitly set the existing value',
                                ];

                                $form->add(
                                    new \IPS\Helpers\Form\Radio(
                                        'rules_numeric_math_operation',
                                        $values['rules_numeric_math_operation'] ?: 'add',
                                        true,
                                        ['options' => $numeric_options],
                                        null,
                                        null,
                                        null,
                                        'rules_numeric_math_operation'
                                    )
                                );
                                $added[] = 'rules_numeric_math_operation';
                                break;

                            case 'array':

                                $array_options = [
                                    'add' => 'Add value to the existing array values',
                                    'remove' => 'Remove value from the existing array values',
                                    'set' => 'Explicitly set the existing array values',
                                ];

                                $form->add(
                                    new \IPS\Helpers\Form\Radio(
                                        'rules_array_operation',
                                        $values['rules_array_operation'] ?: 'add',
                                        true,
                                        ['options' => $array_options],
                                        null,
                                        null,
                                        null,
                                        'rules_array_operation'
                                    )
                                );
                                $added[] = 'rules_array_operation';
                                break;
                        }
                    },
                ],
                'arguments' => [
                    'entity' => [
                        'argtypes' => [
                            'object' => [
                                'description' => $data_field->entityTitle(),
                                'class' => str_replace('-', '\\', $data_field->class),
                            ],
                        ],
                    ],
                    'value' => [
                        'default' => 'manual',
                        'argtypes' => [
                            $data_field->type => [
                                'description' => $data_field->description,
                                'class' => $data_class,

                            ],
                        ],
                        'required' => $data_field->required,
                        'configuration' => [
                            'form' => function ($form, $values) use ($data_field) {
                                $added = [];

                                foreach ($data_field->formElements(null, $values) as $name => $element) {
                                    $form->add($element);
                                    $added[] = $name;
                                }

                                return $added;
                            },
                            'getArg' => function ($values) use ($data_field) {
                                return $data_field->valueFromForm($values);
                            },
                        ],
                    ],
                ],
                'callback' => function ($entity, $value, $values) use ($data_field) {
                    $key = $data_field->column_name;
                    $entity_class = ltrim(str_replace('-', '\\', $data_field->class), '\\');

                    if (!is_object($entity)) {
                        throw new \UnexpectedValueException('Expecting an object and got a ' . gettype($entity));
                    }

                    /* Special exceptions */
                    if ($entity instanceof \IPS\nexus\Customer) {
                        $entity = \IPS\Member::load($entity->member_id);
                    }

                    if (get_class($entity) != $entity_class) {
                        throw new \UnexpectedValueException(
                            'Incorrect entity class (' . get_class($entity) . '). Expecting ' . $entity_class
                        );
                    }

                    switch ($data_field->type) {
                        case 'int':
                        case 'float':

                            switch ($values['rules_numeric_math_operation']) {
                                case 'add':
                                    $entity->setRulesData($key, $entity->getRulesData($key) + $value);
                                    return "value added to " . $data_field->name;

                                case 'subtract':
                                    $entity->setRulesData($key, $entity->getRulesData($key) - $value);
                                    return "value subtracted from " . $data_field->name;

                                case 'multiply':
                                    $entity->setRulesData($key, $entity->getRulesData($key) * $value);
                                    return "value multiplied for " . $data_field->name;

                                case 'divide':
                                    $entity->setRulesData($key, $entity->getRulesData($key) / $value);
                                    return "value divided for " . $data_field->name;
                            }
                            break;

                        case 'array':

                            switch ($values['rules_array_operation']) {
                                case 'add':

                                    $array = (array)$entity->getRulesData($key);
                                    $exists = false;

                                    /**
                                     * Check if this value already exists in the array
                                     */
                                    foreach ($array as $k => $v) {
                                        if ($v === $value) {
                                            $exists = true;
                                            break;
                                        }
                                    }

                                    /* Add it if it doesn't */
                                    if (!$exists) {
                                        $entity->setRulesData($key, array_merge($array, [$value]));
                                        return "value added to array";
                                    }

                                    return "value already exists in the array. no changes made.";

                                case 'remove':

                                    $array = (array)$entity->getRulesData($key);
                                    $removed = false;

                                    /**
                                     * Check if this value exists in the array
                                     */
                                    foreach ($array as $k => $v) {
                                        if ($v === $value) {
                                            unset($array[$k]);
                                            $entity->setRulesData($key, $array);
                                            return "value removed from array";
                                        }
                                    }

                                    return "value didn't exist in the array. no changes made.";
                            }
                            break;
                    }

                    $entity->setRulesData($key, $value);
                    return "value set for " . $data_field->name;
                },
            ];
        }

        return $actions;
    }

}