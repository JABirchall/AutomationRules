//<?php

/* To prevent PHP errors (extending class does not exist) revealing path */
if (!defined('\IPS\SUITE_UNIQUE_KEY')) {
    exit;
}

abstract class rules_hook_ipsApiController extends _HOOK_CLASS_
{

    /**
     * Get all endpoints
     *
     * @return    array
     */
    public static function getAllEndpoints()
    {
        try {
            $endpoints = parent::getAllEndpoints();
            foreach (\IPS\rules\Action\Custom::roots(null) as $action) {
                if ($action->enable_api) {
                    $event = \IPS\rules\Event::load('rules', 'CustomActions', 'custom_action_' . $action->key);
                    $response = [['bool', 'processed', 'TRUE if api request did not result in error']];

                    foreach ($event->rules() as $rule) {
                        foreach ($rule->actions() as $_action) {
                            if ($_action->key == 'set_api_response') {
                                if (isset($_action->data['configuration']['data']) and is_array(
                                        $_action->data['configuration']['data']
                                    )) {
                                    $config = $_action->data['configuration']['data'];
                                    $response[] = [
                                        $config['rules_api_response_type'] ?: 'string',
                                        $config['rules_api_response_key'],
                                        $config['rules_api_response_description'],
                                    ];
                                }
                            }
                        }
                    }

                    $details = [
                        'apiparam' => [],
                        'throws' => [],
                        'return' => [['array']],
                        'apiresponse' => $response,
                    ];

                    foreach ($action->children() as $argument) {
                        switch ($argument->type) {
                            case 'object':
                            case 'array':

                                $type = ' ' . $argument->type;

                                if ($argument->class) {
                                    $class = trim(str_replace('-', '\\', $argument->class), '\\');
                                    if (is_subclass_of($class, 'IPS\Patterns\ActiveRecord')) {
                                        $type = $argument->type == 'array' ? 'array[int]' : 'int';
                                    }
                                }

                                $details['apiparam'][] = [$type, $argument->varname, $argument->description];
                                break;

                            default:

                                $details['apiparam'][] = [
                                    $argument->type,
                                    $argument->varname,
                                    $argument->description,
                                ];
                                break;
                        }
                    }

                    foreach (explode(',', $action->api_methods) as $method) {
                        if (in_array($method, ['GET', 'POST', 'PUT', 'DELETE'])) {
                            $endpoints['rules/actions/' . $method . $action->_id] = [
                                'title' => $method . ' rules/actions/' . $action->_id . ' (' . $action->title . ')',
                                'description' => $action->_description,
                                'details' => $details,
                            ];
                        }
                    }
                }
            }

            return $endpoints;
        } catch (\RuntimeException $e) {
            if (method_exists(get_parent_class(), __FUNCTION__)) {
                return \call_user_func_array('parent::' . __FUNCTION__, \func_get_args());
            } else {
                throw $e;
            }
        }
    }
}
