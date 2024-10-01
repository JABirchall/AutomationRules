<?php
/**
 * @brief        Rules extension: Content
 * @package        Rules for IPS Social Suite
 * @since        25 Feb 2015
 * @version        SVN_VERSION_NUMBER
 */

namespace IPS\rules\extensions\rules\Definitions;

/* To prevent PHP errors (extending class does not exist) revealing path */
if (!defined('\IPS\SUITE_UNIQUE_KEY')) {
    header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}

/**
 * @brief    Rules definitions extension: Content
 */
class _Content
{

    /**
     * @brief    Group events and actions in this extension with other extensions by group name
     */
    public $defaultGroup = 'Content';

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

        $content_events = [
            'content_created' => [
                'arguments' => [
                    'content' => ['argtype' => 'object', 'class' => '\IPS\Content'],
                ],
            ],
            'content_updated' => [
                'arguments' => [
                    'content' => ['argtype' => 'object', 'class' => '\IPS\Content'],
                    'changed' => ['argtype' => 'array'],
                    'new' => ['argtype' => 'bool'],
                ],
            ],
            'content_approved' => [
                'arguments' => [
                    'content' => ['argtype' => 'object', 'class' => '\IPS\Content'],
                    'member' => ['argtype' => 'object', 'class' => '\IPS\Member'],
                ],
            ],
            'content_pinned' => [
                'arguments' => [
                    'content' => ['argtype' => 'object', 'class' => '\IPS\Content'],
                    'member' => ['argtype' => 'object', 'class' => '\IPS\Member'],
                ],
            ],
            'content_unpinned' => [
                'arguments' => [
                    'content' => ['argtype' => 'object', 'class' => '\IPS\Content'],
                    'member' => ['argtype' => 'object', 'class' => '\IPS\Member'],
                ],
            ],
            'content_featured' => [
                'arguments' => [
                    'content' => ['argtype' => 'object', 'class' => '\IPS\Content'],
                    'member' => ['argtype' => 'object', 'class' => '\IPS\Member'],
                ],
            ],
            'content_unfeatured' => [
                'arguments' => [
                    'content' => ['argtype' => 'object', 'class' => '\IPS\Content'],
                    'member' => ['argtype' => 'object', 'class' => '\IPS\Member'],
                ],
            ],
            'content_locked' => [
                'arguments' => [
                    'content' => ['argtype' => 'object', 'class' => '\IPS\Content'],
                    'member' => ['argtype' => 'object', 'class' => '\IPS\Member'],
                ],
            ],
            'content_unlocked' => [
                'arguments' => [
                    'content' => ['argtype' => 'object', 'class' => '\IPS\Content'],
                    'member' => ['argtype' => 'object', 'class' => '\IPS\Member'],
                ],
            ],
            'content_hidden' => [
                'arguments' => [
                    'content' => ['argtype' => 'object', 'class' => '\IPS\Content'],
                    'member' => ['argtype' => 'object', 'class' => '\IPS\Member'],
                    'reason' => ['argtype' => 'string', 'nullable' => true],
                ],
            ],
            'content_unhidden' => [
                'arguments' => [
                    'content' => ['argtype' => 'object', 'class' => '\IPS\Content'],
                    'member' => ['argtype' => 'object', 'class' => '\IPS\Member'],
                ],
            ],
            'content_reported' => [
                'arguments' => [
                    'content' => ['argtype' => 'object', 'class' => '\IPS\Content'],
                    'message' => ['argtype' => 'string'],
                ],
            ],
            'content_deleted' => [
                'arguments' => [
                    'content' => ['argtype' => 'object', 'class' => '\IPS\Content'],
                ],
            ],
            'content_author_changed' => [
                'arguments' => [
                    'content' => ['argtype' => 'object', 'class' => '\IPS\Content'],
                    'oldMember' => ['argtype' => 'object', 'class' => '\IPS\Member'],
                    'newMember' => ['argtype' => 'object', 'class' => '\IPS\Member'],
                ],
            ],
            'content_item_published' => [
                'arguments' => [
                    'item' => ['argtype' => 'object', 'class' => '\IPS\Content\Item'],
                    'member' => ['argtype' => 'object', 'class' => '\IPS\Member'],
                ],
            ],
            'content_item_unpublished' => [
                'arguments' => [
                    'item' => ['argtype' => 'object', 'class' => '\IPS\Content\Item'],
                    'member' => ['argtype' => 'object', 'class' => '\IPS\Member'],
                ],
            ],
            'content_item_moved' => [
                'arguments' => [
                    'item' => ['argtype' => 'object', 'class' => '\IPS\Content\Item'],
                    'oldContainer' => ['argtype' => 'object', 'class' => '\IPS\Node\Model'],
                    'container' => ['argtype' => 'object', 'class' => '\IPS\Node\Model'],
                    'link' => ['argtype' => 'bool'],
                ],
            ],
            'content_item_merging' => [
                'arguments' => [
                    'item' => ['argtype' => 'object', 'class' => '\IPS\Content\Item'],
                    'items' => ['argtype' => 'array', 'class' => '\IPS\Content\Item'],
                ],
            ],
            'content_item_tags_set' => [
                'arguments' => [
                    'item' => ['argtype' => 'object', 'class' => '\IPS\Content\Item'],
                    'tags' => ['argtype' => 'array'],
                ],
            ],
            'content_item_viewed' => [
                'arguments' => [
                    'item' => ['argtype' => 'object', 'class' => '\IPS\Content\Item'],
                ],
            ],
        ];

        $app_events = [];
        $data = [
            'content_events' => $content_events,
            'lang' => $lang,
        ];

        foreach (\IPS\Application::allExtensions('core', 'ContentRouter') as $router) {
            foreach ($router->classes as $contentItemClass) {
                if (is_subclass_of($contentItemClass, '\IPS\Content\Item')) {
                    $contentTitle = ucwords(
                        ($lang->checkKeyExists($contentItemClass::$title) ? $lang->get(
                            $contentItemClass::$title
                        ) : $contentItemClass::$title)
                    );
                    $group = 'Content: ' . ($lang->checkKeyExists(
                            '__app_' . $contentItemClass::$application
                        ) ? $lang->get('__app_' . $contentItemClass::$application) : $contentItemClass::$application);

                    $this->buildEvents($app_events, $contentItemClass, $contentTitle, $group, $data);

                    if (isset ($contentItemClass::$commentClass)) {
                        $commentClass = $contentItemClass::$commentClass;
                        $this->buildEvents($app_events, $commentClass, $contentTitle . ' Comment', $group, $data);
                    }

                    if (isset ($contentItemClass::$reviewClass)) {
                        $reviewClass = $contentItemClass::$reviewClass;
                        $this->buildEvents($app_events, $reviewClass, $contentTitle . ' Review', $group, $data);
                    }
                }
            }
        }

        return array_merge($content_events, $app_events);
    }

    /**
     * Build Events
     */
    protected function buildEvents(&$app_events, $contentClass, $contentTitle, $group, $data)
    {
        $build_data = array_merge($data, [
            'class_key' => md5(ltrim($contentClass, '\\')),
            'contentTitle' => $contentTitle,
            'group' => $group,
            'contentClass' => '\\' . ltrim($contentClass, '\\'),
        ]);

        $this->buildEvent($app_events, 'content_created', $build_data);
        $this->buildEvent($app_events, 'content_updated', $build_data);
        $this->buildEvent($app_events, 'content_approved', $build_data);

        /**
         * App Content Pinnable
         */
        if (in_array('IPS\Content\Pinnable', class_implements($contentClass))) {
            $this->buildEvent($app_events, 'content_pinned', $build_data);
            $this->buildEvent($app_events, 'content_unpinned', $build_data);
        }

        /**
         * App Content Featurable
         */
        if (in_array('IPS\Content\Featurable', class_implements($contentClass))) {
            $this->buildEvent($app_events, 'content_featured', $build_data);
            $this->buildEvent($app_events, 'content_unfeatured', $build_data);
        }

        /**
         * App Content Lockable
         */
        if (in_array('IPS\Content\Lockable', class_implements($contentClass))) {
            $this->buildEvent($app_events, 'content_locked', $build_data);
            $this->buildEvent($app_events, 'content_unlocked', $build_data);
        }

        /**
         * App Content Hideable
         */
        if (in_array('IPS\Content\Hideable', class_implements($contentClass))) {
            $this->buildEvent($app_events, 'content_hidden', $build_data);
            $this->buildEvent($app_events, 'content_unhidden', $build_data);
        }

        /**
         * App Content Reportable
         */
        if (in_array('IPS\Content\Reportable', class_uses($contentClass))) {
            $this->buildEvent($app_events, 'content_reported', $build_data);
        }

        /**
         * Content Items Can Have Tags (even if they don't implement the interface)
         */
        if (is_subclass_of($contentClass, '\IPS\Content\Item')) {
            $this->buildEvent($app_events, 'content_item_tags_set', $build_data);
        }

        $this->buildEvent($app_events, 'content_deleted', $build_data);
        $this->buildEvent($app_events, 'content_author_changed', $build_data);

        if (is_subclass_of($contentClass, '\IPS\Content\Item')) {
            /**
             * App Content Publishable
             */
            if (in_array('IPS\Content\FuturePublishing', class_implements($contentClass))) {
                $this->buildEvent($app_events, 'content_item_published', $build_data);
                $this->buildEvent($app_events, 'content_item_unpublished', $build_data);
            }

            $this->buildEvent($app_events, 'content_item_moved', $build_data);
            $this->buildEvent($app_events, 'content_item_merging', $build_data);
            $this->buildEvent($app_events, 'content_item_viewed', $build_data);
        }
    }

    /**
     * Build Event
     */
    protected function buildEvent(&$app_events, $event_key, $build_data)
    {
        extract($build_data);

        $class_event_key = $event_key . '_' . $class_key;
        $app_events[$class_event_key] = $content_events[$event_key];
        $app_events[$class_event_key]['group'] = $group;

        if (isset($app_events[$class_event_key]['arguments']['content'])) {
            $app_events[$class_event_key]['arguments']['content']['class'] = $contentClass;
        }

        if (isset($app_events[$class_event_key]['arguments']['item'])) {
            $app_events[$class_event_key]['arguments']['item']['class'] = $contentClass;
        }

        /* Event Title */
        $lang->words['rules_Content_event_' . $class_event_key] = sprintf(
            $lang->get('rules_Content_event_' . $event_key . '_'),
            $contentTitle
        );

        /* Argument Titles */
        foreach ($app_events[$class_event_key]['arguments'] as $argname => $argdata) {
            $lang->words['rules_Content_event_' . $class_event_key . '_' . $argname] = sprintf(
                $lang->get('rules_Content_event_' . $event_key . '_' . $argname . '_'),
                $contentTitle
            );
        }
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
        $lang = \IPS\Member::loggedIn()->language();

        $conditions = [
            'content_type' => [
                'callback' => [$this, 'contentType'],
                'configuration' => [
                    'form' => function ($form, $values, $condition) {
                        $lang = \IPS\Member::loggedIn()->language();
                        $options = [];

                        /**
                         * Build list of available content types
                         */
                        foreach (\IPS\Application::allExtensions('core', 'ContentRouter') as $router) {
                            foreach ($router->classes as $contentItemClass) {
                                if (is_subclass_of($contentItemClass, '\IPS\Content\Item')) {
                                    $contentTitle = $lang->checkKeyExists($contentItemClass::$title) ? $lang->get(
                                        $contentItemClass::$title
                                    ) : $contentItemClass::$title;
                                    $options[str_replace('\\', '-', $contentItemClass)] = $lang->addToStack(
                                            '__app_' . $contentItemClass::$application
                                        ) . ' / ' . ucwords($contentTitle) . ' (Content Item)';

                                    if (isset ($contentItemClass::$commentClass)) {
                                        $commentClass = $contentItemClass::$commentClass;
                                        $options[str_replace('\\', '-', $commentClass)] = $lang->addToStack(
                                                '__app_' . $contentItemClass::$application
                                            ) . ' / ' . ucwords($contentTitle) . ' (Comment)';
                                    }

                                    if (isset ($contentItemClass::$reviewClass)) {
                                        $reviewClass = $contentItemClass::$reviewClass;
                                        $options[str_replace('\\', '-', $reviewClass)] = $lang->addToStack(
                                                '__app_' . $contentItemClass::$application
                                            ) . ' / ' . ucwords($contentTitle) . ' (Review)';
                                    }
                                }
                            }
                        }

                        $form->add(
                            new \IPS\Helpers\Form\CheckboxSet(
                                'rules_Content_content_types',
                                $values['rules_Content_content_types'],
                                true,
                                ['options' => $options],
                                null,
                                null,
                                null,
                                'rules_Content_content_types'
                            )
                        );
                        return ['rules_Content_content_types'];
                    },
                ],

                'arguments' => [
                    'content' => [
                        'argtypes' => [
                            'object' => [
                                'description' => 'Content Object',
                                'class' => '\IPS\Content',
                            ],
                        ],
                        'required' => true,
                    ],
                ],
            ],
            'content_status' => [
                'callback' => [$this, 'contentStatus'],
                'configuration' => [
                    'form' => function ($form, $values, $condition) {
                        $lang = \IPS\Member::loggedIn()->language();
                        $options = [];

                        /**
                         * Build list of statuses
                         */
                        $statuses = [
                            'pinned' => 'mod_confirm_pin',
                            'unpinned' => 'mod_confirm_unpin',
                            'featured' => 'mod_confirm_feature',
                            'unfeatured' => 'mod_confirm_unfeature',
                            'hidden' => 'mod_confirm_hide',
                            'unhidden' => 'mod_confirm_unhide',
                            'locked' => 'mod_confirm_lock',
                            'unlocked' => 'mod_confirm_unlock',
                            'published' => 'Published',
                            'is_poll' => 'Has Poll',
                        ];

                        $form->add(
                            new \IPS\Helpers\Form\CheckboxSet(
                                'rules_Content_content_statuses',
                                isset($values['rules_Content_content_statuses']) ? $values['rules_Content_content_statuses'] : null,
                                true,
                                ['options' => $statuses],
                                null,
                                null,
                                null,
                                'rules_Content_content_statuses'
                            )
                        );
                        return ['rules_Content_content_statuses'];
                    },
                ],

                'arguments' => [
                    'content' => [
                        'argtypes' => [
                            'object' => [
                                'description' => 'Content Object',
                                'class' => '\IPS\Content',
                            ],
                        ],
                        'required' => true,
                    ],
                ],
            ],
            'content_author' => [
                'callback' => [$this, 'contentAuthor'],
                'arguments' => [
                    'content' => [
                        'argtypes' => [
                            'object' => [
                                'description' => 'Content Object',
                                'class' => '\IPS\Content',
                            ],
                        ],
                        'required' => true,
                    ],
                    'member' => [
                        'argtypes' => \IPS\rules\Application::argPreset('member'),
                        'configuration' => \IPS\rules\Application::configPreset('member', 'rules_choose_member'),
                        'required' => true,
                    ],
                ],
            ],
            'content_tags' => [
                'callback' => [$this, 'hasTags'],
                'configuration' => [
                    'form' => function ($form, $values) {
                        $tag_options = [
                            'has_any' => 'Any of these tags',
                            'has_all' => 'All of these tags',
                        ];

                        $form->add(
                            new \IPS\Helpers\Form\Radio(
                                'rules_Content_check_tags_type',
                                isset($values['rules_Content_check_tags_type']) ? $values['rules_Content_check_tags_type'] : 'has_any',
                                true,
                                ['options' => $tag_options]
                            )
                        );
                        $form->add(
                            new \IPS\Helpers\Form\YesNo(
                                'rules_Content_check_tags_prefix',
                                isset($values['rules_Content_check_tags_prefix']) ? $values['rules_Content_check_tags_prefix'] : false,
                                false
                            )
                        );
                    },
                ],
                'arguments' => [
                    'item' => [
                        'argtypes' => [
                            'object' => [
                                'description' => 'Content Object',
                                'class' => '\IPS\Content\Item',
                            ],
                        ],
                        'required' => true,
                    ],
                    'tags' => [
                        'default' => 'manual',
                        'argtypes' => \IPS\rules\Application::argPreset('tags'),
                        'configuration' => \IPS\rules\Application::configPreset(
                            'tags',
                            'rules_Content_tags_stack',
                            true
                        ),
                        'required' => true,
                    ],
                ],
            ],
            'content_attributes' => [
                'callback' => [$this, 'checkContentAttributes'],
                'configuration' => [
                    'form' => function (&$form, $values, $operation) {
                        $attribute_options = [
                            'comments' => 'rules_content_attribute_comments',
                            'reviews' => 'rules_content_attribute_reviews',
                            'unapproved_comments' => 'rules_content_attribute_unapproved_comments',
                            'unapproved_reviews' => 'rules_content_attribute_unapproved_reviews',
                            'views' => 'rules_content_attribute_views',
                            'poll_votes' => 'rules_content_attribute_poll_votes',
                            'created' => 'rules_content_attribute_created',
                            'updated' => 'rules_content_attribute_updated',
                            'last_post' => 'rules_content_attribute_last_post',
                        ];

                        $attribute_toggles = [
                            'comments' => [
                                'rules_Content_attribute_compare_type_value',
                                'rules_Content_attribute_compare_value',
                            ],
                            'reviews' => [
                                'rules_Content_attribute_compare_type_value',
                                'rules_Content_attribute_compare_value',
                            ],
                            'unapproved_comments' => [
                                'rules_Content_attribute_compare_type_value',
                                'rules_Content_attribute_compare_value',
                            ],
                            'unapproved_reviews' => [
                                'rules_Content_attribute_compare_type_value',
                                'rules_Content_attribute_compare_value',
                            ],
                            'views' => [
                                'rules_Content_attribute_compare_type_value',
                                'rules_Content_attribute_compare_value',
                            ],
                            'poll_votes' => [
                                'rules_Content_attribute_compare_type_value',
                                'rules_Content_attribute_compare_value',
                            ],
                            'created' => ['rules_Content_attribute_compare_type_date'],
                            'updated' => ['rules_Content_attribute_compare_type_date'],
                            'last_post' => ['rules_Content_attribute_compare_type_date'],
                        ];

                        $value_compare_options = [
                            '<' => 'Less than',
                            '>' => 'More than',
                            '=' => 'Equal to',
                        ];

                        $date_compare_options = [
                            '<' => 'Before',
                            '>' => 'After',
                            '=' => 'On',
                            '?' => 'Within the last',
                        ];

                        $date_toggles = [
                            '<' => ['rules_Content_attribute_compare_date'],
                            '>' => ['rules_Content_attribute_compare_date'],
                            '=' => ['rules_Content_attribute_compare_date'],
                            '?' => [
                                'rules_Content_attribute_compare_minutes',
                                'rules_Content_attribute_compare_hours',
                                'rules_Content_attribute_compare_days',
                                'rules_Content_attribute_compare_months',
                                'rules_Content_attribute_compare_years',
                            ],
                        ];

                        $form->add(
                            new \IPS\Helpers\Form\Radio(
                                'rules_Content_content_attribute',
                                isset($values['rules_Content_content_attribute']) ? $values['rules_Content_content_attribute'] : null,
                                true,
                                ['options' => $attribute_options, 'toggles' => $attribute_toggles],
                                null,
                                null,
                                null,
                                'rules_Content_content_attribute'
                            )
                        );
                        $form->add(
                            new \IPS\Helpers\Form\Radio(
                                'rules_Content_attribute_compare_type_value',
                                isset($values['rules_Content_attribute_compare_type_value']) ? $values['rules_Content_attribute_compare_type_value'] : null,
                                false,
                                ['options' => $value_compare_options],
                                null,
                                null,
                                null,
                                'rules_Content_attribute_compare_type_value'
                            )
                        );
                        $form->add(
                            new \IPS\Helpers\Form\Number(
                                'rules_Content_attribute_compare_value',
                                isset($values['rules_Content_attribute_compare_value']) ? $values['rules_Content_attribute_compare_value'] : 0,
                                false,
                                [],
                                null,
                                null,
                                null,
                                'rules_Content_attribute_compare_value'
                            )
                        );
                        $form->add(
                            new \IPS\Helpers\Form\Radio(
                                'rules_Content_attribute_compare_type_date',
                                isset($values['rules_Content_attribute_compare_type_date']) ? $values['rules_Content_attribute_compare_type_date'] : null,
                                false,
                                ['options' => $date_compare_options, 'toggles' => $date_toggles],
                                null,
                                null,
                                null,
                                'rules_Content_attribute_compare_type_date'
                            )
                        );
                        $form->add(
                            new \IPS\Helpers\Form\Date(
                                'rules_Content_attribute_compare_date',
                                isset($values['rules_Content_attribute_compare_date']) ? \IPS\DateTime::ts(
                                    $values['rules_Content_attribute_compare_date']
                                ) : null,
                                false,
                                [],
                                null,
                                null,
                                null,
                                'rules_Content_attribute_compare_date'
                            )
                        );
                        $form->add(
                            new \IPS\Helpers\Form\Number(
                                'rules_Content_attribute_compare_minutes',
                                isset($values['rules_Content_attribute_compare_minutes']) ? $values['rules_Content_attribute_compare_minutes'] : 0,
                                true,
                                [],
                                null,
                                null,
                                null,
                                'rules_Content_attribute_compare_minutes'
                            )
                        );
                        $form->add(
                            new \IPS\Helpers\Form\Number(
                                'rules_Content_attribute_compare_hours',
                                isset($values['rules_Content_attribute_compare_hours']) ? $values['rules_Content_attribute_compare_hours'] : 0,
                                true,
                                [],
                                null,
                                null,
                                null,
                                'rules_Content_attribute_compare_hours'
                            )
                        );
                        $form->add(
                            new \IPS\Helpers\Form\Number(
                                'rules_Content_attribute_compare_days',
                                isset($values['rules_Content_attribute_compare_days']) ? $values['rules_Content_attribute_compare_days'] : 0,
                                true,
                                [],
                                null,
                                null,
                                null,
                                'rules_Content_attribute_compare_days'
                            )
                        );
                        $form->add(
                            new \IPS\Helpers\Form\Number(
                                'rules_Content_attribute_compare_months',
                                isset($values['rules_Content_attribute_compare_months']) ? $values['rules_Content_attribute_compare_months'] : 0,
                                true,
                                [],
                                null,
                                null,
                                null,
                                'rules_Content_attribute_compare_months'
                            )
                        );
                        $form->add(
                            new \IPS\Helpers\Form\Number(
                                'rules_Content_attribute_compare_years',
                                isset($values['rules_Content_attribute_compare_years']) ? $values['rules_Content_attribute_compare_years'] : 0,
                                true,
                                [],
                                null,
                                null,
                                null,
                                'rules_Content_attribute_compare_years'
                            )
                        );
                    },
                    'saveValues' => function (&$values) {
                        if (isset($values['rules_Content_attribute_compare_date']) and $values['rules_Content_attribute_compare_date'] instanceof \IPS\DateTime) {
                            $values['rules_Content_attribute_compare_date'] = $values['rules_Content_attribute_compare_date']->getTimestamp(
                            );
                        }
                    },
                ],
                'arguments' => [
                    'content' => [
                        'argtypes' => [
                            'object' => [
                                'description' => 'Content Object',
                                'class' => '\IPS\Content\Item',
                            ],
                        ],
                        'required' => true,
                    ],
                ],
            ],
        ];

        foreach (\IPS\Application::allExtensions('core', 'ContentRouter') as $router) {
            foreach ($router->classes as $contentItemClass) {
                if (is_subclass_of($contentItemClass, '\IPS\Content\Item')) {
                    $contentTitle = ucwords(
                        ($lang->checkKeyExists($contentItemClass::$title) ? $lang->get(
                            $contentItemClass::$title
                        ) : $contentItemClass::$title)
                    );
                    $group = 'Content: ' . ($lang->checkKeyExists(
                            '__app_' . $contentItemClass::$application
                        ) ? $lang->get('__app_' . $contentItemClass::$application) : $contentItemClass::$application);
                    $class_key = md5(ltrim($contentItemClass, '\\'));

                    if (isset ($contentItemClass::$containerNodeClass)) {
                        $nodeClass = $contentItemClass::$containerNodeClass;
                        $nodeTitle = $lang->checkKeyExists($nodeClass::$nodeTitle) ? $lang->get(
                            $nodeClass::$nodeTitle
                        ) : $nodeClass::$nodeTitle;
                        $lang->words['rules_Content_conditions_content_container_' . $class_key] = sprintf(
                            $lang->get('rules_Content_conditions_content_container'),
                            $contentTitle,
                            mb_strtolower($nodeTitle)
                        );
                        $lang->words['rules_Content_conditions_content_container_' . $class_key . '_content'] = sprintf(
                            $lang->get('rules_Content_conditions_content_container_content'),
                            $contentTitle
                        );
                        $lang->words['rules_Content_conditions_content_container_' . $class_key . '_container'] = sprintf(
                            $lang->get('rules_Content_conditions_content_container_container'),
                            $nodeTitle
                        );
                        $lang->words['rules_Content_content_containers_' . $class_key] = sprintf(
                            $lang->get('rules_Content_content_containers'),
                            $nodeTitle
                        );

                        $conditions['content_container_' . $class_key] = [
                            'group' => $group,
                            'callback' => [$this, 'contentContainer'],
                            'arguments' => [
                                'container' => [
                                    'default' => 'manual',
                                    'required' => true,
                                    'configuration' => [
                                        'form' => function ($form, $values) use (
                                            $nodeClass,
                                            $contentItemClass,
                                            $contentTitle,
                                            $class_key
                                        ) {
                                            $form->add(
                                                new \IPS\Helpers\Form\Node(
                                                    'rules_Content_content_containers_' . $class_key,
                                                    isset($values['rules_Content_content_containers_' . $class_key]) ? $values['rules_Content_content_containers_' . $class_key] : null,
                                                    true,
                                                    ['class' => $nodeClass, 'multiple' => true],
                                                    null,
                                                    null,
                                                    null,
                                                    'rules_Content_content_containers_' . $class_key
                                                )
                                            );
                                            return ['rules_Content_content_containers_' . $class_key];
                                        },
                                        'saveValues' => function (&$values) use ($class_key) {
                                            if (is_array($values['rules_Content_content_containers_' . $class_key])) {
                                                $values['rules_Content_content_containers_' . $class_key] = array_keys(
                                                    $values['rules_Content_content_containers_' . $class_key]
                                                );
                                            }
                                        },
                                        'getArg' => function ($values) use ($class_key) {
                                            return $values['rules_Content_content_containers_' . $class_key];
                                        },
                                    ],
                                    'argtypes' => [
                                        'object' => [
                                            'description' => 'A ' . mb_strtolower($contentTitle) . ' ' . mb_strtolower(
                                                    $nodeTitle
                                                ) . ' node',
                                            'class' => $nodeClass,
                                            'converter' => function ($node) {
                                                return [$node->_id];
                                            },
                                        ],
                                        'array' => [
                                            'description' => 'An array of ' . mb_strtolower(
                                                    $contentTitle
                                                ) . ' ' . mb_strtolower($nodeTitle) . ' nodes',
                                            'class' => $nodeClass,
                                            'converter' => function ($nodes) {
                                                $_nodes = [];
                                                if (is_array($nodes)) {
                                                    foreach ($nodes as $node) {
                                                        if ($node instanceof \IPS\Node\Model) {
                                                            $_nodes[] = $node->_id;
                                                        } else {
                                                            if ($node) {
                                                                $_nodes[] = $node;
                                                            }
                                                        }
                                                    }
                                                }
                                                return $_nodes;
                                            },
                                        ],
                                    ],
                                ],
                                'content' => [
                                    'required' => true,
                                    'argtypes' => [
                                        'object' => [
                                            'description' => $contentTitle . " to check",
                                            'class' => '\\' . ltrim($contentItemClass, '\\'),
                                        ],
                                    ],
                                    'configuration' => \IPS\rules\Application::configPreset(
                                        'item',
                                        'rules_choose_item',
                                        true,
                                        ['class' => $contentItemClass]
                                    ),
                                ],

                            ],
                        ];

                        $lang->words['rules_Content_conditions_container_' . $class_key] = sprintf(
                            $lang->get('rules_Content_conditions_container'),
                            mb_strtolower($nodeTitle)
                        );
                        $lang->words['rules_Content_conditions_container_' . $class_key . '_container'] = $lang->get(
                            'rules_Content_conditions_container_container'
                        );
                        $lang->words['rules_Content_conditions_container_' . $class_key . '_containers'] = sprintf(
                            $lang->get('rules_Content_conditions_container_containers'),
                            $nodeTitle
                        );
                        $lang->words['rules_Content_containers_' . $class_key] = sprintf(
                            $lang->get('rules_Content_content_containers'),
                            $nodeTitle
                        );
                        $conditions['container_' . $class_key] = [
                            'group' => $group,
                            'callback' => [$this, 'checkContainer'],
                            'arguments' => [
                                'container' => [
                                    'required' => true,
                                    'configuration' => [
                                        'form' => function ($form, $values) use (
                                            $nodeClass,
                                            $contentItemClass,
                                            $contentTitle,
                                            $class_key
                                        ) {
                                            $form->add(
                                                new \IPS\Helpers\Form\Node(
                                                    'rules_Content_container',
                                                    isset($values['rules_Content_container']) ? $values['rules_Content_container'] : null,
                                                    true,
                                                    ['class' => $nodeClass, 'multiple' => false],
                                                    null,
                                                    null,
                                                    null,
                                                    'rules_Content_container'
                                                )
                                            );
                                            return ['rules_Content_container'];
                                        },
                                        'saveValues' => function (&$values) use ($nodeClass) {
                                            if (isset($values['rules_Content_container']) and $values['rules_Content_container'] instanceof $nodeClass) {
                                                $values['rules_Content_container'] = $values['rules_Content_container']->_id;
                                            }
                                        },
                                        'getArg' => function ($values) {
                                            return $values['rules_Content_container'];
                                        },
                                    ],
                                    'argtypes' => [
                                        'object' => [
                                            'description' => 'A ' . mb_strtolower($contentTitle) . ' ' . mb_strtolower(
                                                    $nodeTitle
                                                ) . ' node',
                                            'class' => $nodeClass,
                                        ],
                                    ],
                                ],
                                'containers' => [
                                    'default' => 'manual',
                                    'required' => true,
                                    'configuration' => [
                                        'form' => function ($form, $values) use (
                                            $nodeClass,
                                            $contentItemClass,
                                            $contentTitle,
                                            $class_key
                                        ) {
                                            $form->add(
                                                new \IPS\Helpers\Form\Node(
                                                    'rules_Content_containers_' . $class_key,
                                                    isset($values['rules_Content_containers_' . $class_key]) ? $values['rules_Content_containers_' . $class_key] : null,
                                                    true,
                                                    ['class' => $nodeClass, 'multiple' => true],
                                                    null,
                                                    null,
                                                    null,
                                                    'rules_Content_containers_' . $class_key
                                                )
                                            );
                                            return ['rules_Content_containers_' . $class_key];
                                        },
                                        'saveValues' => function (&$values) use ($class_key) {
                                            if (is_array($values['rules_Content_containers_' . $class_key])) {
                                                $values['rules_Content_containers_' . $class_key] = array_keys(
                                                    $values['rules_Content_containers_' . $class_key]
                                                );
                                            }
                                        },
                                        'getArg' => function ($values) use ($class_key) {
                                            return $values['rules_Content_containers_' . $class_key];
                                        },
                                    ],
                                    'argtypes' => [
                                        'object' => [
                                            'description' => 'A ' . mb_strtolower($contentTitle) . ' ' . mb_strtolower(
                                                    $nodeTitle
                                                ) . ' node',
                                            'class' => $nodeClass,
                                            'converter' => function ($node) {
                                                return [$node->_id];
                                            },
                                        ],
                                        'array' => [
                                            'description' => 'An array of ' . mb_strtolower(
                                                    $contentTitle
                                                ) . ' ' . mb_strtolower($nodeTitle) . ' nodes',
                                            'class' => $nodeClass,
                                            'converter' => function ($nodes) {
                                                $_nodes = [];
                                                if (is_array($nodes)) {
                                                    foreach ($nodes as $node) {
                                                        if ($node instanceof \IPS\Node\Model) {
                                                            $_nodes[] = $node->_id;
                                                        } else {
                                                            if ($node) {
                                                                $_nodes[] = $node;
                                                            }
                                                        }
                                                    }
                                                }
                                                return $_nodes;
                                            },
                                        ],
                                    ],
                                ],
                            ],
                        ];
                    }
                }
            }
        }

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
        $self = $this;

        $actions = [
            'set_content_tags' => [
                'callback' => [$this, 'setTags'],
                'configuration' => [
                    'form' => function ($form, $values) {
                        $tag_options = [
                            'add' => 'Add Tags',
                            'remove' => 'Remove Tags',
                            'set' => 'Set Tags Explicitly',
                        ];

                        $form->add(
                            new \IPS\Helpers\Form\Radio(
                                'rules_Content_modify_tags_type',
                                isset($values['rules_Content_modify_tags_type']) ? $values['rules_Content_modify_tags_type'] : 'add',
                                true,
                                ['options' => $tag_options]
                            )
                        );
                    },
                ],
                'arguments' => [
                    'content' => [
                        'argtypes' => [
                            'object' => [
                                'description' => 'Content Object',
                                'class' => '\IPS\Content',
                            ],
                        ],
                        'required' => true,
                    ],
                    'tags' => [
                        'default' => 'manual',
                        'argtypes' => \IPS\rules\Application::argPreset('tags'),
                        'configuration' => \IPS\rules\Application::configPreset(
                            'tags',
                            'rules_Content_tags_stack',
                            true
                        ),
                        'required' => true,
                    ],
                ],
            ],
            'pin_content' => [
                'callback' => [$this, 'pinContent'],
                'arguments' => [
                    'content' => [
                        'argtypes' => [
                            'object' => [
                                'description' => 'Content Object',
                                'class' => '\IPS\Content',
                            ],
                        ],
                        'required' => true,
                    ],
                ],
            ],
            'unpin_content' => [
                'callback' => [$this, 'unpinContent'],
                'arguments' => [
                    'content' => [
                        'argtypes' => [
                            'object' => [
                                'description' => 'Content Object',
                                'class' => '\IPS\Content',
                            ],
                        ],
                        'required' => true,
                    ],
                ],
            ],
            'feature_content' => [
                'callback' => [$this, 'featureContent'],
                'arguments' => [
                    'content' => [
                        'argtypes' => [
                            'object' => [
                                'description' => 'Content Object',
                                'class' => '\IPS\Content',
                            ],
                        ],
                        'required' => true,
                    ],
                ],
            ],
            'unfeature_content' => [
                'callback' => [$this, 'unfeatureContent'],
                'arguments' => [
                    'content' => [
                        'argtypes' => [
                            'object' => [
                                'description' => 'Content Object',
                                'class' => '\IPS\Content',
                            ],
                        ],
                        'required' => true,
                    ],
                ],
            ],
            'lock_content' => [
                'callback' => [$this, 'lockContent'],
                'arguments' => [
                    'content' => [
                        'argtypes' => [
                            'object' => [
                                'description' => 'Content Object',
                                'class' => '\IPS\Content',
                            ],
                        ],
                        'required' => true,
                    ],
                ],
            ],
            'unlock_content' => [
                'callback' => [$this, 'unlockContent'],
                'arguments' => [
                    'content' => [
                        'argtypes' => [
                            'object' => [
                                'description' => 'Content Object',
                                'class' => '\IPS\Content',
                            ],
                        ],
                        'required' => true,
                    ],
                ],
            ],
            'hide_content' => [
                'callback' => [$this, 'hideContent'],
                'arguments' => [
                    'content' => [
                        'argtypes' => [
                            'object' => [
                                'description' => 'Content Object',
                                'class' => '\IPS\Content',
                            ],
                        ],
                        'required' => true,
                    ],
                    'member' => [
                        'argtypes' => \IPS\rules\Application::argPreset('member'),
                        'configuration' => \IPS\rules\Application::configPreset('member', 'rules_choose_member'),
                        'required' => true,

                    ],
                    'reason' => [
                        'argtypes' => ['string' => ['description' => 'The reason for hiding the content']],
                        'required' => false,
                        'configuration' => [
                            'form' => function ($form, $values, $condition) {
                                $form->add(
                                    new \IPS\Helpers\Form\Text(
                                        'rules_Content_reason',
                                        isset($values['rules_Content_reason']) ? $values['rules_Content_reason'] : null,
                                        false,
                                        [],
                                        null,
                                        null,
                                        null,
                                        'rules_Content_reason'
                                    )
                                );
                                return ['rules_Content_reason'];
                            },
                            'getArg' => function ($values) {
                                return $values['rules_Content_reason'];
                            },
                        ],
                    ],
                ],
            ],
            'unhide_content' => [
                'callback' => [$this, 'unhideContent'],
                'arguments' => [
                    'content' => [
                        'argtypes' => [
                            'object' => [
                                'description' => 'Content Object',
                                'class' => '\IPS\Content',
                            ],
                        ],
                        'required' => true,
                    ],
                    'member' => [
                        'argtypes' => \IPS\rules\Application::argPreset('member'),
                        'configuration' => \IPS\rules\Application::configPreset('member', 'rules_choose_member'),
                        'required' => false,
                    ],
                ],
            ],
            'delete_content' => [
                'callback' => [$this, 'deleteContent'],
                'arguments' => [
                    'content' => [
                        'argtypes' => [
                            'object' => [
                                'description' => 'Content Object',
                                'class' => '\IPS\Content',
                            ],
                        ],
                        'required' => true,
                    ],
                ],
            ],
            'change_author' => [
                'callback' => [$this, 'changeAuthor'],
                'arguments' => [
                    'content' => [
                        'argtypes' => [
                            'object' => [
                                'description' => 'Content Object',
                                'class' => '\IPS\Content',
                            ],
                        ],
                        'required' => true,
                    ],
                    'author' => [
                        'argtypes' => \IPS\rules\Application::argPreset('member'),
                        'configuration' => \IPS\rules\Application::configPreset('member', 'rules_choose_member'),
                        'required' => true,
                    ],
                ],
            ],
        ];

        foreach (\IPS\Application::allExtensions('core', 'ContentRouter') as $router) {
            foreach ($router->classes as $contentItemClass) {
                if (is_subclass_of($contentItemClass, '\IPS\Content\Item')) {
                    $contentTitle = ucwords(
                        $lang->checkKeyExists($contentItemClass::$title) ? $lang->get(
                            $contentItemClass::$title
                        ) : $contentItemClass::$title
                    );
                    $group = 'Content: ' . ($lang->checkKeyExists(
                            '__app_' . $contentItemClass::$application
                        ) ? $lang->get('__app_' . $contentItemClass::$application) : $contentItemClass::$application);
                    $class_key = md5(ltrim($contentItemClass, '\\'));

                    if (isset ($contentItemClass::$containerNodeClass)) {
                        $nodeClass = $contentItemClass::$containerNodeClass;
                        $nodeTitle = $lang->checkKeyExists($nodeClass::$nodeTitle) ? $lang->get(
                            $nodeClass::$nodeTitle
                        ) : $nodeClass::$nodeTitle;
                        $lang->words['rules_Content_content_container_' . $class_key] = sprintf(
                            $lang->get('rules_Content_content_container'),
                            $nodeTitle
                        );

                        $lang->words['rules_Content_actions_move_content_' . $class_key] = sprintf(
                            $lang->get('rules_Content_actions_move_content'),
                            mb_strtolower($contentTitle),
                            mb_strtolower($nodeTitle)
                        );
                        $lang->words['rules_Content_actions_move_content_' . $class_key . '_item'] = sprintf(
                            $lang->get('rules_Content_actions_move_content_item'),
                            $contentTitle
                        );
                        $lang->words['rules_Content_actions_move_content_' . $class_key . '_container'] = sprintf(
                            $lang->get('rules_Content_actions_move_content_container'),
                            $lang->get($nodeClass::$nodeTitle)
                        );

                        $lang->words['rules_Content_actions_create_content_' . $class_key] = sprintf(
                            $lang->get('rules_Content_actions_create_content'),
                            mb_strtolower($contentTitle),
                            mb_strtolower($nodeTitle)
                        );
                        $lang->words['rules_Content_actions_create_content_' . $class_key . '_container'] = sprintf(
                            $lang->get('rules_Content_actions_create_content_container'),
                            $contentTitle
                        );
                        $lang->words['rules_Content_actions_create_content_' . $class_key . '_author'] = sprintf(
                            $lang->get('rules_Content_actions_create_content_author'),
                            $contentTitle
                        );
                        $lang->words['rules_Content_actions_create_content_' . $class_key . '_title'] = sprintf(
                            $lang->get('rules_Content_actions_create_content_title'),
                            $contentTitle
                        );
                        $lang->words['rules_Content_actions_create_content_' . $class_key . '_content'] = sprintf(
                            $lang->get('rules_Content_actions_create_content_content'),
                            $contentTitle
                        );
                        $lang->words['rules_Content_actions_create_content_' . $class_key . '_tags'] = sprintf(
                            $lang->get('rules_Content_actions_create_content_tags'),
                            $contentTitle
                        );

                        $lang->words['rules_Content_actions_create_content_comment_' . $class_key] = sprintf(
                            $lang->get('rules_Content_actions_create_content_comment'),
                            mb_strtolower($contentTitle),
                            mb_strtolower($nodeTitle)
                        );
                        $lang->words['rules_Content_actions_create_content_comment_' . $class_key . '_item'] = sprintf(
                            $lang->get('rules_Content_actions_create_content_comment_item'),
                            $contentTitle
                        );
                        $lang->words['rules_Content_actions_create_content_comment_' . $class_key . '_author'] = sprintf(
                            $lang->get('rules_Content_actions_create_content_comment_author'),
                            $contentTitle
                        );
                        $lang->words['rules_Content_actions_create_content_comment_' . $class_key . '_content'] = sprintf(
                            $lang->get('rules_Content_actions_create_content_comment_content'),
                            $contentTitle
                        );

                        /**
                         * Move Content
                         */
                        $actions['move_content_' . $class_key] = [
                            'group' => $group,
                            'callback' => [$this, 'moveContent'],
                            'configuration' => [
                                'form' => function ($form, $values) {
                                    $form->add(
                                        new \IPS\Helpers\Form\YesNo(
                                            'rules_Content_move_content_link',
                                            isset($values['rules_Content_move_content_link']) ? $values['rules_Content_move_content_link'] : null,
                                            true
                                        )
                                    );
                                },
                            ],
                            'arguments' => [
                                'container' => [
                                    'default' => 'manual',
                                    'required' => true,
                                    'configuration' => [
                                        'form' => function ($form, $values) use (
                                            $nodeClass,
                                            $contentItemClass,
                                            $contentTitle,
                                            $class_key
                                        ) {
                                            $form->add(
                                                new \IPS\Helpers\Form\Node(
                                                    'rules_Content_content_container_' . $class_key,
                                                    isset($values['rules_Content_content_container_' . $class_key]) ? $values['rules_Content_content_container_' . $class_key] : null,
                                                    true,
                                                    [
                                                        'class' => $nodeClass,
                                                        'multiple' => false,
                                                        'subnodes' => false,
                                                    ],
                                                    null,
                                                    null,
                                                    null,
                                                    'rules_Content_content_container_' . $class_key
                                                )
                                            );
                                            return ['rules_Content_content_container_' . $class_key];
                                        },
                                        'saveValues' => function (&$values) use ($class_key) {
                                            if (isset($values['rules_Content_content_container_' . $class_key]) and $values['rules_Content_content_container_' . $class_key] instanceof \IPS\Node\Model) {
                                                $values['rules_Content_content_container_' . $class_key] = $values['rules_Content_content_container_' . $class_key]->_id;
                                            }
                                        },
                                        'getArg' => function ($values) use ($nodeClass, $class_key) {
                                            try {
                                                $container = $nodeClass::load(
                                                    $values['rules_Content_content_container_' . $class_key]
                                                );
                                                return $container;
                                            } catch (\OutOfRangeException $e) {
                                                return null;
                                            }
                                        },
                                    ],
                                    'argtypes' => [
                                        'object' => [
                                            'description' => 'A ' . mb_strtolower($contentTitle) . ' ' . mb_strtolower(
                                                    $nodeTitle
                                                ) . ' node',
                                            'class' => $nodeClass,
                                        ],
                                    ],
                                ],
                                'item' => [
                                    'required' => true,
                                    'argtypes' => [
                                        'object' => [
                                            'description' => $contentTitle . " to move",
                                            'class' => '\\' . ltrim($contentItemClass, '\\'),
                                        ],
                                    ],
                                    'configuration' => \IPS\rules\Application::configPreset(
                                        'item',
                                        'rules_choose_item',
                                        true,
                                        ['class' => $contentItemClass]
                                    ),
                                ],
                            ],
                        ];

                        /**
                         * Create New Content Item
                         */
                        $actions['create_content_' . $class_key] = [
                            'group' => $group,
                            'configuration' => [
                                'form' => function ($form, $values, $operation) use ($lang) {
                                    $form->add(
                                        new \IPS\Helpers\Form\YesNo(
                                            'rules_Content_increase_posts',
                                            isset($values['rules_Content_increase_posts']) ? $values['rules_Content_increase_posts'] : null,
                                            true
                                        )
                                    );
                                    $form->add(
                                        new \IPS\Helpers\Form\Text(
                                            'rules_Content_guest_name',
                                            isset ($values['rules_Content_guest_name']) ? $values['rules_Content_guest_name'] : 'Guest',
                                            false
                                        )
                                    );
                                    $form->add(
                                        new \IPS\Helpers\Form\YesNo(
                                            'rules_Content_use_created_handler',
                                            isset($values['rules_Content_use_created_handler']) ? $values['rules_Content_use_created_handler'] : false,
                                            false,
                                            ['togglesOn' => [$form->id . '_rules_Content_created_handler']]
                                        )
                                    );

                                    $lang->words['rules_Content_created_handler_desc'] = $lang->get(
                                            'rules_Content_created_handler_description'
                                        ) . \IPS\rules\Application::eventArgInfo($operation->event());
                                    $form->add(
                                        new \IPS\Helpers\Form\Codemirror(
                                            'rules_Content_created_handler',
                                            isset($values['rules_Content_created_handler']) ? $values['rules_Content_created_handler'] : "//<?php\n\n/* custom processing of \$createdContent... */\n\n",
                                            false,
                                            ['mode' => 'php']
                                        )
                                    );
                                },
                            ],
                            'callback' => function ($container, $author, $title, $content, $tags, $values, $arg_map) use
                            (
                                $nodeClass,
                                $contentItemClass,
                                $self
                            ) {
                                if (!($container instanceof \IPS\Node\Model)) {
                                    throw new \UnexpectedValueException("container is not a node");
                                }

                                if (get_class($container) !== $nodeClass) {
                                    throw new \UnexpectedValueException("container is not the expected class");
                                }

                                if (!($author instanceof \IPS\Member)) {
                                    throw new \UnexpectedValueException("author is not a valid member");
                                }

                                /* Create item */
                                $item = $contentItemClass::createItem(
                                    $author,
                                    $author->ip_address,
                                    \IPS\DateTime::ts(time()),
                                    $container
                                );

                                /* Set item title */
                                if ($titleColumn = $contentItemClass::$databaseColumnMap['title']) {
                                    $item->$titleColumn = $title;
                                }

                                /* Save content to item if it supports it */
                                if ($bodyColumn = $contentItemClass::$databaseColumnMap['content']) {
                                    $item->$bodyColumn = $content;
                                }

                                $item->save();

                                /* Save content as first comment if required */
                                if ($contentItemClass::$firstCommentRequired and isset($contentItemClass::$commentClass)) {
                                    $commentClass = $contentItemClass::$commentClass;
                                    $comment = $commentClass::create(
                                        $item,
                                        $content,
                                        true,
                                        $values['rules_Content_guest_name'],
                                        (bool)$values['rules_Content_increase_posts'],
                                        $author,
                                        $now
                                    );
                                    if (isset($contentItemClass::$databaseColumnMap['first_comment_id'])) {
                                        $firstCommentIdColumn = $contentItemClass::$databaseColumnMap['first_comment_id'];
                                        $commentIdColumn = $commentClass::$databaseColumnId;
                                        $item->$firstCommentIdColumn = $comment->$commentIdColumn;
                                        $item->save();
                                    }
                                }

                                /* Save tags */
                                if (!empty ($tags)) {
                                    /* Set tags through our rules action to account for non-logged in members */
                                    $self->setTags($item, $tags, ['rules_Content_modify_tags_type' => 'set']);
                                }

                                /* Custom PHP Code Handler */
                                if ($values['rules_Content_use_created_handler']) {
                                    $arg_map['createdContent'] = $item;
                                    $self->evaluate($values['rules_Content_created_handler'], $arg_map);
                                }

                                return "content created";
                            },
                            'arguments' => [
                                'container' => [
                                    'required' => true,
                                    'configuration' => [
                                        'form' => function ($form, $values) use (
                                            $nodeClass,
                                            $contentItemClass,
                                            $contentTitle,
                                            $class_key
                                        ) {
                                            $form->add(
                                                new \IPS\Helpers\Form\Node(
                                                    'rules_Content_content_container_' . $class_key,
                                                    isset($values['rules_Content_content_container_' . $class_key]) ? $values['rules_Content_content_container_' . $class_key] : null,
                                                    true,
                                                    [
                                                        'class' => $nodeClass,
                                                        'multiple' => false,
                                                        'subnodes' => false,
                                                    ],
                                                    null,
                                                    null,
                                                    null,
                                                    'rules_Content_content_container_' . $class_key
                                                )
                                            );
                                            return ['rules_Content_content_container_' . $class_key];
                                        },
                                        'saveValues' => function (&$values) use ($class_key) {
                                            if (isset($values['rules_Content_content_container_' . $class_key]) and $values['rules_Content_content_container_' . $class_key] instanceof \IPS\Node\Model) {
                                                $values['rules_Content_content_container_' . $class_key] = $values['rules_Content_content_container_' . $class_key]->_id;
                                            }
                                        },
                                        'getArg' => function ($values) use ($nodeClass, $class_key) {
                                            try {
                                                $container = $nodeClass::load(
                                                    $values['rules_Content_content_container_' . $class_key]
                                                );
                                                return $container;
                                            } catch (\OutOfRangeException $e) {
                                                return null;
                                            }
                                        },
                                    ],
                                    'argtypes' => [
                                        'object' => [
                                            'description' => 'A ' . mb_strtolower($contentTitle) . ' ' . mb_strtolower(
                                                    $nodeTitle
                                                ) . ' node',
                                            'class' => $nodeClass,
                                        ],
                                    ],
                                ],
                                'author' => [
                                    'argtypes' => \IPS\rules\Application::argPreset('member'),
                                    'configuration' => \IPS\rules\Application::configPreset(
                                        'member',
                                        'rules_choose_member'
                                    ),
                                    'required' => true,
                                ],
                                'title' => [
                                    'default' => 'manual',
                                    'required' => true,
                                    'argtypes' => [
                                        'string' => [
                                            'description' => 'The content title',
                                        ],
                                    ],
                                    'configuration' => [
                                        'form' => function ($form, $values) {
                                            $form->add(
                                                new \IPS\Helpers\Form\Text(
                                                    'rules_Content_content_title',
                                                    isset($values['rules_Content_content_title']) ? $values['rules_Content_content_title'] : null,
                                                    true,
                                                    [],
                                                    null,
                                                    null,
                                                    null,
                                                    'rules_Content_content_title'
                                                )
                                            );
                                            return ['rules_Content_content_title'];
                                        },
                                        'getArg' => function ($values) {
                                            return $values['rules_Content_content_title'];
                                        },
                                    ],
                                ],
                                'content' => [
                                    'default' => 'manual',
                                    'required' => true,
                                    'argtypes' => [
                                        'string' => [
                                            'description' => 'The content body',
                                        ],
                                    ],
                                    'configuration' => [
                                        'form' => function ($form, $values) {
                                            $form->add(
                                                new \IPS\Helpers\Form\Editor(
                                                    'rules_Content_content_body',
                                                    isset($values['rules_Content_content_body']) ? $values['rules_Content_content_body'] : null,
                                                    true,
                                                    ['app' => 'rules', 'key' => 'Generic']
                                                )
                                            );
                                            return [$form->id . '_rules_Content_content_body'];
                                        },
                                        'getArg' => function ($values) {
                                            return $values['rules_Content_content_body'];
                                        },
                                    ],
                                ],
                                'tags' => [
                                    'default' => 'manual',
                                    'argtypes' => \IPS\rules\Application::argPreset('tags'),
                                    'configuration' => \IPS\rules\Application::configPreset(
                                        'tags',
                                        'rules_Content_tags_stack',
                                        false
                                    ),
                                    'required' => false,
                                ],
                            ],
                        ];

                        if (isset ($contentItemClass::$commentClass)) {
                            /**
                             * Create New Content Comment
                             */
                            $actions['create_content_comment_' . $class_key] = [
                                'group' => $group,
                                'configuration' => [
                                    'form' => function ($form, $values, $operation) use ($lang) {
                                        $form->add(
                                            new \IPS\Helpers\Form\YesNo(
                                                'rules_Content_increase_posts',
                                                isset($values['rules_Content_increase_posts']) ? $values['rules_Content_increase_posts'] : null,
                                                true
                                            )
                                        );
                                        $form->add(
                                            new \IPS\Helpers\Form\Text(
                                                'rules_Content_guest_name',
                                                isset ($values['rules_Content_guest_name']) ? $values['rules_Content_guest_name'] : 'Guest',
                                                false
                                            )
                                        );
                                        $form->add(
                                            new \IPS\Helpers\Form\YesNo(
                                                'rules_Content_use_created_handler',
                                                isset($values['rules_Content_use_created_handler']) ? $values['rules_Content_use_created_handler'] : false,
                                                false,
                                                ['togglesOn' => [$form->id . '_rules_Content_created_handler']]
                                            )
                                        );

                                        $lang->words['rules_Content_created_handler_desc'] = $lang->get(
                                                'rules_Content_created_handler_description'
                                            ) . \IPS\rules\Application::eventArgInfo($operation->event());
                                        $form->add(
                                            new \IPS\Helpers\Form\Codemirror(
                                                'rules_Content_created_handler',
                                                isset($values['rules_Content_created_handler']) ? $values['rules_Content_created_handler'] : "//<?php\n\n/* custom processing of \$createdContent... */\n\n",
                                                false,
                                                ['mode' => 'php']
                                            )
                                        );
                                    },
                                ],
                                'callback' => function ($item, $author, $content, $values, $arg_map) use (
                                    $contentItemClass,
                                    $self
                                ) {
                                    if (!($item instanceof \IPS\Content\Item)) {
                                        throw new \UnexpectedValueException("invalid content item");
                                    }

                                    if (get_class($item) !== $contentItemClass) {
                                        throw new \UnexpectedValueException("content item is not the correct class");
                                    }

                                    if (!($author instanceof \IPS\Member)) {
                                        throw new \UnexpectedValueException("author is not a valid member");
                                    }

                                    $commentClass = $contentItemClass::$commentClass;
                                    $comment = $commentClass::create
                                    (
                                        $item,
                                        $content,
                                        false,
                                        $values['rules_Content_guest_name'],
                                        (bool)$values['rules_Content_increase_posts'],
                                        $author,
                                        \IPS\DateTime::ts(time())
                                    );

                                    /* Custom PHP Code Handler */
                                    if ($values['rules_Content_use_created_handler']) {
                                        $arg_map['createdContent'] = $comment;
                                        $self->evaluate($values['rules_Content_created_handler'], $arg_map);
                                    }

                                    return "content comment created";
                                },
                                'arguments' => [
                                    'item' => [
                                        'required' => true,
                                        'argtypes' => [
                                            'object' => [
                                                'description' => $contentTitle . " to post comment to",
                                                'class' => '\\' . ltrim($contentItemClass, '\\'),
                                            ],
                                        ],
                                        'configuration' => \IPS\rules\Application::configPreset(
                                            'item',
                                            'rules_choose_item',
                                            true,
                                            ['class' => $contentItemClass]
                                        ),
                                    ],
                                    'author' => [
                                        'argtypes' => \IPS\rules\Application::argPreset('member'),
                                        'configuration' => \IPS\rules\Application::configPreset(
                                            'member',
                                            'rules_choose_member'
                                        ),
                                        'required' => true,
                                    ],
                                    'content' => [
                                        'default' => 'manual',
                                        'required' => true,
                                        'argtypes' => [
                                            'string' => [
                                                'description' => 'The content body',
                                            ],
                                        ],
                                        'configuration' => [
                                            'form' => function ($form, $values) {
                                                $form->add(
                                                    new \IPS\Helpers\Form\Editor(
                                                        'rules_Content_content_body',
                                                        isset($values['rules_Content_content_body']) ? $values['rules_Content_content_body'] : null,
                                                        true,
                                                        ['app' => 'rules', 'key' => 'Generic']
                                                    )
                                                );
                                                return [$form->id . '_rules_Content_content_body'];
                                            },
                                            'getArg' => function ($values) {
                                                return $values['rules_Content_content_body'];
                                            },
                                        ],
                                    ],
                                ],
                            ];
                        }
                    }
                }
            }
        }

        return $actions;
    }

    /**
     * Check Content Types
     */
    public function contentType($content, $values)
    {
        if (!($content instanceof \IPS\Content)) {
            throw new \UnexpectedValueException('Invalid content');
        }

        return in_array(str_replace('\\', '-', get_class($content)), (array)$values['rules_Content_content_types']);
    }

    /**
     * Check Content Status
     */
    public function contentStatus($content, $values)
    {
        if (!($content instanceof \IPS\Content)) {
            throw new \UnexpectedValueException('Invalid content');
        }

        $hasStatus = false;

        foreach ((array)$values['rules_Content_content_statuses'] as $status) {
            switch ($status) {
                case 'is_poll':
                    $hasStatus = (($content instanceof \IPS\Content\Polls) and $content->getPoll());
                    break;

                case 'pinned':
                    $hasStatus = $content->mapped('pinned');
                    break;

                case 'unpinned':
                    $hasStatus = (isset($content::$databaseColumnMap['pinned']) and !$content->mapped('pinned'));
                    break;

                case 'featured':
                    $hasStatus = $content->mapped('featured');
                    break;

                case 'unfeatured':
                    $hasStatus = (isset($content::$databaseColumnMap['featured']) and !$content->mapped('featured'));
                    break;

                case 'hidden':
                    $hasStatus = $content->hidden();
                    break;

                case 'unhidden':
                    $hasStatus = !$content->hidden();
                    break;

                case 'locked':
                    $hasStatus = ($content->mapped('locked') or $content->mapped('status') == 'closed');
                    break;

                case 'unlocked':
                    $hasStatus = !($content->mapped('locked') or $content->mapped('status') == 'closed');
                    break;

                case 'published':
                    $hasStatus = (!$content->isFutureDate() and !$content->hidden());
                    break;
            }

            if (!$hasStatus) {
                break;
            }
        }

        return (bool)$hasStatus;
    }

    /**
     * Content Has Author
     */
    public function contentAuthor($content, $member, $values)
    {
        if (!($content instanceof \IPS\Content)) {
            throw new \UnexpectedValueException('Invalid content');
        }

        if (!($member instanceof \IPS\Member)) {
            throw new \UnexpectedValueException('Invalid member');
        }

        return $content->author()->member_id == $member->member_id;
    }

    /**
     * Check If Content Has Tags
     */
    public function hasTags($item, $tags, $values)
    {
        if (!($item instanceof \IPS\Content\Item)) {
            throw new \UnexpectedValueException('Invalid content item');
        }

        $_tags = $item->tags();
        $prefix = $item->prefix();
        if (isset($values['rules_Content_check_tags_prefix']) and $values['rules_Content_check_tags_prefix'] == true and $prefix !== null) {
            $_tags[] = $prefix;
        }

        switch ($values['rules_Content_check_tags_type']) {
            case 'has_any':

                $matched = array_intersect($tags, $_tags);
                if (count($matched)) {
                    return true;
                }

                return false;

            case 'has_all':

                $matched = array_intersect($tags, $_tags);
                if (count($tags) == count($matched)) {
                    return true;
                }

                return false;
        }
    }

    /**
     * Check Content Attributes
     */
    public function checkContentAttributes($content, $values)
    {
        if (!($content instanceof \IPS\Content)) {
            throw new \UnexpectedValueException('Invalid content');
        }

        switch ($values['rules_Content_content_attribute']) {
            case 'comments':

                $amount = (int)$content->mapped('num_comments');
                break;

            case 'reviews':

                $amount = (int)$content->mapped('num_reviews');
                break;

            case 'unapproved_comments':

                $amount = (int)$content->mapped('unapproved_comments');
                break;

            case 'unapproved_reviews':

                $amount = (int)$content->mapped('unapproved_reviews');
                break;

            case 'views':

                $amount = (int)$content->mapped('views');
                break;

            case 'poll_votes':

                $amount = 0;
                if ($content instanceof \IPS\Content\Polls) {
                    if ($poll = $content->getPoll()) {
                        $amount = count($poll->getVotes());
                    }
                }
                break;

            case 'created':

                $date = \IPS\DateTime::ts($content->mapped('date'));
                break;

            case 'updated':

                $date = \IPS\DateTime::ts($content->mapped('updated'));
                break;

            case 'last_post':

                $date = \IPS\DateTime::ts(max($content->mapped('last_comment'), $content->mapped('last_review')));
                break;
        }


        switch ($values['rules_Content_content_attribute']) {
            case 'comments':
            case 'reviews':
            case 'unapproved_comments':
            case 'unapproved_reviews':
            case 'views':
            case 'poll_votes':

                $value = $values['rules_Content_attribute_compare_value'];
                switch ($values['rules_Content_attribute_compare_type_value']) {
                    case '<':
                        return $amount < $value;

                    case '>':
                        return $amount > $value;

                    case '=':
                        return $amount == $value;

                    default:
                        return false;
                }
                break;

            case 'created':
            case 'updated':
            case 'last_post':

                $value = $values['rules_Content_attribute_compare_date'];
                switch ($values['rules_Content_attribute_compare_type_date']) {
                    case '?':
                        $value = strtotime(
                            '-' . intval($values['rules_Content_attribute_compare_minutes']) . ' minutes ' .
                            '-' . intval($values['rules_Content_attribute_compare_hours']) . ' hours ' .
                            '-' . intval($values['rules_Content_attribute_compare_days']) . ' days ' .
                            '-' . intval($values['rules_Content_attribute_compare_months']) . ' months ' .
                            '-' . intval($values['rules_Content_attribute_compare_years']) . ' years '
                        );
                        return $date->getTimestamp() > $value;

                    case '>':
                        return $date->getTimestamp() > $value;

                    case '<':
                        return $date->getTimestamp() < $value;

                    case '=':
                        $value = \IPS\DateTime::ts($value);
                        return (
                            $value->y == $date->y and
                            $value->m == $date->m and
                            $value->d == $date->d
                        );
                }
        }

        return false;
    }

    /**
     * Check If Content Is In Container
     */
    public function contentContainer($containers, $content, $values)
    {
        if (!($content instanceof \IPS\Content\Item)) {
            throw new \UnexpectedValueException('Invalid content item');
        }

        if (!isset($content::$containerNodeClass)) {
            throw new \InvalidArgumentException('Content item does not support containers');
        }

        if ($container = $content->containerWrapper(true)) {
            return in_array($container->_id, (array)$containers);
        }

        return false;
    }

    /**
     * Check For Specific Container
     */
    public function checkContainer($container, $containers, $values)
    {
        if (!($container instanceof \IPS\Node\Model)) {
            throw new \UnexpectedValueException('Invalid container');
        }

        return in_array($container->_id, (array)$containers);
    }

    /**
     * Set Content Tags
     */
    public function setTags($content, $tags, $values)
    {
        if (!($content instanceof \IPS\Content\Item)) {
            throw new \UnexpectedValueException('Invalid content item');
        }

        /* Tags cannot be saved with a NULL member_id */
        if (\IPS\Member::loggedIn()->member_id === null) {
            $anonymous = true;
            \IPS\Member::loggedIn()->member_id = 0;
        }

        switch ($values['rules_Content_modify_tags_type']) {
            case 'add':

                $_tags = array_filter(
                    array_unique(
                        array_merge((array)$content->tags(), ['prefix' => $content->prefix()], (array)$tags)
                    )
                );
                $content->setTags($_tags);
                $status = "content tags added";
                break;

            case 'remove':

                $_tags = array_filter(
                    array_unique(array_merge((array)$content->tags(), ['prefix' => $content->prefix()]))
                );
                foreach ((array)$tags as $tag) {
                    if (($i = array_search($tag, $_tags)) !== false) {
                        unset($_tags[$i]);
                    }
                }
                $content->setTags($_tags);
                $status = "content tags removed";
                break;

            case 'set':

                $content->setTags($tags);
                $status = "content tags set";
                break;
        }

        /* If member is anonymous, re-set member_id to NULL */
        if (isset($anonymous)) {
            \IPS\Member::loggedIn()->member_id = null;
        }

        return $status;
    }

    /**
     * Pin Content
     */
    public function pinContent($content)
    {
        if (!is_object($content)) {
            throw new \UnexpectedValueException('content is not an object');
        }

        if (!($content instanceof \IPS\Content)) {
            throw new \UnexpectedValueException('not a content class: ' . get_class($content));
        }

        if ($pinned = $content::$databaseColumnMap['pinned']) {
            $content->$pinned = true;
            $content->save();
            $content->modActionEvent('pin');

            return 'content pinned';
        }

        return 'content not pinnable';
    }

    /**
     * UnPin Content
     */
    public function unpinContent($content)
    {
        if (!is_object($content)) {
            throw new \UnexpectedValueException('content is not an object');
        }

        if (!($content instanceof \IPS\Content)) {
            throw new \UnexpectedValueException('not a content class: ' . get_class($content));
        }

        if ($pinned = $content::$databaseColumnMap['pinned']) {
            $content->$pinned = false;
            $content->save();
            $content->modActionEvent('unpin');

            return 'content unpinned';
        }

        return 'content not pinnable';
    }

    /**
     * Feature Content
     */
    public function featureContent($content)
    {
        if (!is_object($content)) {
            throw new \UnexpectedValueException('content is not an object');
        }

        if (!($content instanceof \IPS\Content)) {
            throw new \UnexpectedValueException('not a content class: ' . get_class($content));
        }

        if ($featured = $content::$databaseColumnMap['featured']) {
            $content->$featured = true;
            $content->save();
            $content->modActionEvent('feature');

            return 'content featured';
        }

        return 'content not featurable';
    }

    /**
     * Unfeature Content
     */
    public function unfeatureContent($content)
    {
        if (!is_object($content)) {
            throw new \UnexpectedValueException('content is not an object');
        }

        if (!($content instanceof \IPS\Content)) {
            throw new \UnexpectedValueException('not a content class: ' . get_class($content));
        }

        if ($featured = $content::$databaseColumnMap['featured']) {
            $content->$featured = false;
            $content->save();
            $content->modActionEvent('unfeature');

            return 'content unfeatured';
        }

        return 'content not featurable';
    }

    /**
     * Lock Content
     */
    public function lockContent($content)
    {
        if (!is_object($content)) {
            throw new \UnexpectedValueException('content is not an object');
        }

        if (!($content instanceof \IPS\Content)) {
            throw new \UnexpectedValueException('not a content class: ' . get_class($content));
        }

        if ($locked = $content::$databaseColumnMap['locked']) {
            $content->$locked = true;
            $content->save();
            $content->modActionEvent('lock');
            return 'content locked';
        } else {
            if ($status = $content::$databaseColumnMap['status']) {
                $content->$status = 'closed';
                $content->save();
                $content->modActionEvent('lock');

                return 'content locked';
            }
        }

        return 'content not lockable';
    }

    /**
     * Unlock Content
     */
    public function unlockContent($content)
    {
        if (!is_object($content)) {
            throw new \UnexpectedValueException('content is not an object');
        }

        if (!($content instanceof \IPS\Content)) {
            throw new \UnexpectedValueException('not a content class: ' . get_class($content));
        }

        if ($locked = $content::$databaseColumnMap['locked']) {
            $content->$locked = false;
            $content->save();
            $content->modActionEvent('unlock');
            return 'content unlocked';
        } else {
            if ($status = $content::$databaseColumnMap['status']) {
                $content->$status = 'open';
                $content->save();
                $content->modActionEvent('unlock');

                return 'content unlocked';
            }
        }

        return 'content not lockable';
    }

    /**
     * Hide Content
     */
    public function hideContent($content, $member, $reason)
    {
        if (!is_object($content)) {
            throw new \UnexpectedValueException('content is not an object');
        }

        if (!($content instanceof \IPS\Content)) {
            throw new \UnexpectedValueException('not a content class: ' . get_class($content));
        }

        $content->hide($member, $reason);
        return 'content hidden';
    }

    /**
     * Unhide Content
     */
    public function unhideContent($content, $member)
    {
        if (!is_object($content)) {
            throw new \UnexpectedValueException('content is not an object');
        }

        if (!($content instanceof \IPS\Content)) {
            throw new \UnexpectedValueException('not a content class: ' . get_class($content));
        }

        $content->unhide($member);
        return 'content unhidden';
    }

    /**
     * Delete Content
     */
    public function deleteContent($content)
    {
        if (!is_object($content)) {
            throw new \UnexpectedValueException('content is not an object');
        }

        if (!($content instanceof \IPS\Content)) {
            throw new \UnexpectedValueException('not a content class: ' . get_class($content));
        }

        $content->$pinned = false;
        $content->delete();
        return 'content deleted';
    }

    /**
     * Change Author
     */
    public function changeAuthor($content, $author)
    {
        if (!is_object($content)) {
            throw new \UnexpectedValueException('content is not an object');
        }

        if (!($content instanceof \IPS\Content)) {
            throw new \UnexpectedValueException('not a content class: ' . get_class($content));
        }

        if (!($author instanceof \IPS\Member)) {
            throw new \UnexpectedValueException('invalid new author');
        }

        if ($author_col = $content::$databaseColumnMap['author']) {
            $content->$author_col = $author->member_id;
            $content->save();
            return 'author changed';
        }

        return "can't change content author";
    }

    /**
     * Move Content
     */
    public function moveContent($container, $content, $values)
    {
        if (!is_object($content)) {
            throw new \UnexpectedValueException('not a valid content object');
        }

        if (!($content instanceof \IPS\Content\Item)) {
            throw new \UnexpectedValueException('not a valid content class: ' . get_class($content));
        }

        if (!isset($content::$containerNodeClass)) {
            throw new \UnexpectedValueException('content class doesn\'t support containers: ' . get_class($content));
        }

        if (!($container instanceof \IPS\Node\Model)) {
            throw new \UnexpectedValueException('invalid content container');
        }

        if (get_class($container) != ltrim($content::$containerNodeClass, '\\')) {
            throw new \UnexpectedValueException('container node is not the correct class');
        }

        if ($content_container = $content->containerWrapper(true) and $content_container->_id === $container->_id) {
            return "content not moved, it's already there";
        }

        $content->move($container, $values['rules_Content_move_content_link']);
        return 'content moved';
    }

    /**
     * Evaluate PHP
     *
     * Originally this was inline with the code that calls it, however it was
     * tripping the GoDaddy malware detection on app upload and causing
     * users hosted with them problems when trying to install/update the app.
     *
     * @param string $code The code to evaluate
     * @param array $args Variables to extract
     * @return    mixed
     */
    public function evaluate($code, $args)
    {
        extract($args);
        return @eval($code);
    }

}