<?php
/**
 * @brief        Rules extension: Members
 * @package        Rules for IPS Social Suite
 * @since        20 Feb 2015
 * @version        SVN_VERSION_NUMBER
 */

namespace IPS\rules\extensions\rules\Definitions;

/* To prevent PHP errors (extending class does not exist) revealing path */
if (!defined('\IPS\SUITE_UNIQUE_KEY')) {
    header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}

/**
 * @brief    Content Router extension: Definitions
 */
class _Members
{

    /**
     * Definition Group
     */
    public $defaultGroup = 'members';

    /**
     * @brief    Triggerable Events
     */
    public function events()
    {
        $memberArg = [
            'argtype' => 'object',
            'class' => '\IPS\Member',
        ];

        $events = [
            'memberSync_onLogin' => [
                'arguments' => [
                    'member' => $memberArg,
                ],
            ],
            'memberSync_onLogout' => [
                'arguments' => [
                    'member' => $memberArg,
                ],
            ],
            'memberSync_onCreateAccount' => [
                'arguments' => [
                    'member' => $memberArg,
                ],
            ],
            'memberSync_onProfileUpdate' => [
                'arguments' => [
                    'member' => $memberArg,
                    'changed' => ['argtype' => 'array'],
                ],
            ],
            'memberSync_onSetAsSpammer' => [
                'arguments' => [
                    'member' => $memberArg,
                ],
            ],
            'member_not_spammer' => [
                'arguments' => [
                    'member' => $memberArg,
                ],
            ],
            'memberSync_onValidate' => [
                'arguments' => [
                    'member' => $memberArg,
                ],
            ],
            'memberSync_onMerge' => [
                'arguments' => [
                    'member' => $memberArg,
                    'mergedMember' => $memberArg,
                ],
            ],
            'memberSync_onDelete' => [
                'arguments' => [
                    'member' => $memberArg,
                ],
            ],
            'member_banned' => [
                'arguments' => [
                    'member' => $memberArg,
                ],
            ],
            'member_unbanned' => [
                'arguments' => [
                    'member' => $memberArg,
                ],
            ],
            'reputation_given' => [
                'arguments' => [
                    'member' => $memberArg,
                    'giver' => $memberArg,
                    'content' => ['argtype' => 'object', 'class' => '\IPS\Content'],
                    'reptype' => ['argtype' => 'int'],
                ],
            ],
            'member_warned' => [
                'arguments' => [
                    'warning' => ['argtype' => 'object', 'class' => '\IPS\core\Warnings\Warning'],
                    'member' => $memberArg,
                    'moderator' => $memberArg,
                ],
            ],
            'content_recounted' => [
                'arguments' => [
                    'member' => $memberArg,
                    'count' => ['argtype' => 'int'],
                ],
            ],
        ];

        return $events;
    }

    /**
     * Operational Conditions
     */
    public function conditions()
    {
        return [
            'check_member' => [
                'callback' => [$this, 'checkMember'],
                'configuration' => [
                    'form' => function ($form, $values) {
                        $members = [];
                        $rules_choose_members = isset($values['rules_choose_members']) ? (array)$values['rules_choose_members'] : [];
                        foreach ($rules_choose_members as $member_id) {
                            if ($member_id) {
                                try {
                                    $members[] = \IPS\Member::load($member_id);
                                } catch (\Exception $e) {
                                }
                            }
                        }

                        $form->add(
                            new \IPS\Helpers\Form\Member(
                                'rules_choose_members',
                                $members,
                                true,
                                ['multiple' => null],
                                null,
                                null,
                                null,
                                'rules_choose_members'
                            )
                        );
                    },
                    'saveValues' => function (&$values) {
                        $members = [];
                        $rules_choose_members = isset($values['rules_choose_members']) ? (array)$values['rules_choose_members'] : [];
                        foreach ($rules_choose_members as $member) {
                            $members[] = $member->member_id;
                        }
                        $values['rules_choose_members'] = $members;
                    },
                ],
                'arguments' => [
                    'member' => [
                        'argtypes' => \IPS\rules\Application::argPreset('member'),
                        'required' => true,
                    ],
                ],
            ],
            'member_has_group' => [
                'configuration' => [
                    'form' => function (&$form, $values, $operation) {
                        $options = [];
                        foreach (\IPS\Member\Group::groups() as $group) {
                            $options[$group->g_id] = $group->name;
                        }

                        $form->add(
                            new \IPS\Helpers\Form\CheckboxSet(
                                'rules_Members_member_groups',
                                isset($values['rules_Members_member_groups']) ? $values['rules_Members_member_groups'] : null,
                                true,
                                ['options' => $options],
                                null,
                                null,
                                null,
                                'rules_Members_member_groups'
                            )
                        );
                    },
                ],
                'callback' => [$this, 'checkMemberGroup'],
                'arguments' => [
                    'member' => [
                        'argtypes' => \IPS\rules\Application::argPreset('member'),
                        'configuration' => \IPS\rules\Application::configPreset('member', 'rules_choose_member'),
                        'required' => true,
                    ],
                ],
            ],
            'member_status' => [
                'configuration' => [
                    'form' => function (&$form, $values, $operation) {
                        $status_options = [
                            'online' => 'rules_member_online',
                            'validating' => 'rules_member_validating',
                            'spammer' => 'rules_member_spammer',
                            'banned_perm' => 'rules_member_banned_perm',
                            'banned_temp' => 'rules_member_banned_temp',
                            'warnlevel' => 'rules_member_warnlevel',
                        ];
                        $form->add(
                            new \IPS\Helpers\Form\Radio(
                                'rules_Members_member_status',
                                isset($values['rules_Members_member_status']) ? $values['rules_Members_member_status'] : null,
                                true,
                                ['options' => $status_options],
                                null,
                                null,
                                null,
                                'rules_Members_member_status'
                            )
                        );
                    },
                ],
                'callback' => [$this, 'checkMemberStatus'],
                'arguments' => [
                    'member' => [
                        'argtypes' => \IPS\rules\Application::argPreset('member'),
                        'configuration' => \IPS\rules\Application::configPreset('member', 'rules_choose_member'),
                        'required' => true,
                    ],
                ],
            ],
            'member_attributes' => [
                'configuration' => [
                    'form' => function (&$form, $values, $operation) {
                        $attribute_options = [
                            'photo' => 'rules_member_attribute_photo',
                            'signature' => 'rules_member_attribute_signature',
                            'followers' => 'rules_member_attribute_followers',
                            'reputation' => 'rules_member_attribute_reputation',
                            'posts' => 'rules_member_attribute_posts',
                            'pviews' => 'rules_member_attribute_profile_views',
                            'joined' => 'rules_member_attribute_joined',
                            'birthdate' => 'rules_member_attribute_birthdate',
                            'last_activity' => 'rules_member_attribute_last_activity',
                            'last_post' => 'rules_member_attribute_last_post',
                        ];

                        $attribute_toggles = [
                            'followers' => [
                                'rules_Members_attribute_compare_type_value',
                                'rules_Members_attribute_compare_value',
                            ],
                            'reputation' => [
                                'rules_Members_attribute_compare_type_value',
                                'rules_Members_attribute_compare_value',
                            ],
                            'posts' => [
                                'rules_Members_attribute_compare_type_value',
                                'rules_Members_attribute_compare_value',
                            ],
                            'pviews' => [
                                'rules_Members_attribute_compare_type_value',
                                'rules_Members_attribute_compare_value',
                            ],
                            'joined' => ['rules_Members_attribute_compare_type_date'],
                            'joined' => ['rules_Members_attribute_compare_type_date'],
                            'birthdate' => ['rules_Members_attribute_compare_type_date'],
                            'last_activity' => ['rules_Members_attribute_compare_type_date'],
                            'last_post' => ['rules_Members_attribute_compare_type_date'],
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
                            '<' => ['rules_Members_attribute_compare_date'],
                            '>' => ['rules_Members_attribute_compare_date'],
                            '=' => ['rules_Members_attribute_compare_date'],
                            '?' => [
                                'rules_Members_attribute_compare_minutes',
                                'rules_Members_attribute_compare_hours',
                                'rules_Members_attribute_compare_days',
                                'rules_Members_attribute_compare_months',
                                'rules_Members_attribute_compare_years',
                            ],
                        ];

                        $form->add(
                            new \IPS\Helpers\Form\Radio(
                                'rules_Members_member_attribute',
                                isset($values['rules_Members_member_attribute']) ? $values['rules_Members_member_attribute'] : null,
                                true,
                                ['options' => $attribute_options, 'toggles' => $attribute_toggles],
                                null,
                                null,
                                null,
                                'rules_Members_member_attribute'
                            )
                        );
                        $form->add(
                            new \IPS\Helpers\Form\Radio(
                                'rules_Members_attribute_compare_type_value',
                                isset($values['rules_Members_attribute_compare_type_value']) ? $values['rules_Members_attribute_compare_type_value'] : null,
                                false,
                                ['options' => $value_compare_options],
                                null,
                                null,
                                null,
                                'rules_Members_attribute_compare_type_value'
                            )
                        );
                        $form->add(
                            new \IPS\Helpers\Form\Number(
                                'rules_Members_attribute_compare_value',
                                isset($values['rules_Members_attribute_compare_value']) ? $values['rules_Members_attribute_compare_value'] : 0,
                                false,
                                [],
                                null,
                                null,
                                null,
                                'rules_Members_attribute_compare_value'
                            )
                        );
                        $form->add(
                            new \IPS\Helpers\Form\Radio(
                                'rules_Members_attribute_compare_type_date',
                                isset($values['rules_Members_attribute_compare_type_date']) ? $values['rules_Members_attribute_compare_type_date'] : null,
                                false,
                                ['options' => $date_compare_options, 'toggles' => $date_toggles],
                                null,
                                null,
                                null,
                                'rules_Members_attribute_compare_type_date'
                            )
                        );
                        $form->add(
                            new \IPS\Helpers\Form\Date(
                                'rules_Members_attribute_compare_date',
                                isset($values['rules_Members_attribute_compare_date']) ? \IPS\DateTime::ts(
                                    $values['rules_Members_attribute_compare_date']
                                ) : null,
                                false,
                                [],
                                null,
                                null,
                                null,
                                'rules_Members_attribute_compare_date'
                            )
                        );
                        $form->add(
                            new \IPS\Helpers\Form\Number(
                                'rules_Members_attribute_compare_minutes',
                                isset($values['rules_Members_attribute_compare_minutes']) ? $values['rules_Members_attribute_compare_minutes'] : 0,
                                true,
                                [],
                                null,
                                null,
                                null,
                                'rules_Members_attribute_compare_minutes'
                            )
                        );
                        $form->add(
                            new \IPS\Helpers\Form\Number(
                                'rules_Members_attribute_compare_hours',
                                isset($values['rules_Members_attribute_compare_hours']) ? $values['rules_Members_attribute_compare_hours'] : 0,
                                true,
                                [],
                                null,
                                null,
                                null,
                                'rules_Members_attribute_compare_hours'
                            )
                        );
                        $form->add(
                            new \IPS\Helpers\Form\Number(
                                'rules_Members_attribute_compare_days',
                                isset($values['rules_Members_attribute_compare_days']) ? $values['rules_Members_attribute_compare_days'] : 0,
                                true,
                                [],
                                null,
                                null,
                                null,
                                'rules_Members_attribute_compare_days'
                            )
                        );
                        $form->add(
                            new \IPS\Helpers\Form\Number(
                                'rules_Members_attribute_compare_months',
                                isset($values['rules_Members_attribute_compare_months']) ? $values['rules_Members_attribute_compare_months'] : 0,
                                true,
                                [],
                                null,
                                null,
                                null,
                                'rules_Members_attribute_compare_months'
                            )
                        );
                        $form->add(
                            new \IPS\Helpers\Form\Number(
                                'rules_Members_attribute_compare_years',
                                isset($values['rules_Members_attribute_compare_years']) ? $values['rules_Members_attribute_compare_years'] : 0,
                                true,
                                [],
                                null,
                                null,
                                null,
                                'rules_Members_attribute_compare_years'
                            )
                        );
                    },
                    'saveValues' => function (&$values) {
                        if (isset($values['rules_Members_attribute_compare_date']) and $values['rules_Members_attribute_compare_date'] instanceof \IPS\DateTime) {
                            $values['rules_Members_attribute_compare_date'] = $values['rules_Members_attribute_compare_date']->getTimestamp(
                            );
                        }
                    },
                ],
                'callback' => [$this, 'checkMemberAttributes'],
                'arguments' => [
                    'member' => [
                        'argtypes' => \IPS\rules\Application::argPreset('member'),
                        'configuration' => \IPS\rules\Application::configPreset('member', 'rules_choose_member'),
                        'required' => true,
                    ],
                ],
            ],
            'member_following' => [
                'callback' => [$this, 'memberFollowing'],
                'arguments' => [
                    'member' => [
                        'argtypes' => \IPS\rules\Application::argPreset('member'),
                        'configuration' => \IPS\rules\Application::configPreset('member', 'rules_choose_member'),
                        'required' => true,
                    ],
                    'member2' => [
                        'argtypes' => \IPS\rules\Application::argPreset('member'),
                        'configuration' => \IPS\rules\Application::configPreset('member', 'rules_choose_member2'),
                        'required' => true,
                    ],
                ],
            ],
            'member_ignoring' => [
                'callback' => [$this, 'memberIgnoring'],
                'configuration' => [
                    'form' => function ($form, $values) {
                        $ignore_options = [
                            'topics' => 'Content Posts',
                            'messages' => 'Messages',
                            'signatures' => 'Signatures',
                        ];

                        $form->add(
                            new \IPS\Helpers\Form\Radio(
                                'rules_Members_ignore_type',
                                $values['rules_Members_ignore_type'] ?: 'topics',
                                true,
                                ['options' => $ignore_options]
                            )
                        );
                    },
                ],
                'arguments' => [
                    'member' => [
                        'argtypes' => \IPS\rules\Application::argPreset('member'),
                        'configuration' => \IPS\rules\Application::configPreset('member', 'rules_choose_member'),
                        'required' => true,
                    ],
                    'member2' => [
                        'argtypes' => \IPS\rules\Application::argPreset('member'),
                        'configuration' => \IPS\rules\Application::configPreset('member', 'rules_choose_member2'),
                        'required' => true,
                    ],
                ],
            ],
        ];
    }

    /**
     * @brief    Triggerable Actions
     */
    public function actions()
    {
        $actions = [
            'change_primary_group' => [
                'callback' => [$this, 'changePrimaryGroup'],
                'configuration' => [
                    'form' => function ($form, $values, $action) {
                        $form->add(
                            new \IPS\Helpers\Form\Select(
                                'rules_Members_member_primary_group',
                                isset($values['rules_Members_member_primary_group']) ? $values['rules_Members_member_primary_group'] : null,
                                true,
                                [
                                    'options' => \IPS\Member\Group::groups(
                                        \IPS\Member::loggedIn()->hasAcpRestriction(
                                            'core',
                                            'members',
                                            'member_add_admin'
                                        ),
                                        false
                                    ),
                                    'parse' => 'normal',
                                ]
                            )
                        );
                    },
                ],
                'arguments' => [
                    'member' => [
                        'argtypes' => \IPS\rules\Application::argPreset('member'),
                        'configuration' => \IPS\rules\Application::configPreset('member', 'rules_choose_member'),
                        'required' => true,
                    ],
                ],
            ],
            'add_secondary_groups' => [
                'callback' => [$this, 'addSecondaryGroups'],
                'configuration' => [
                    'form' => function ($form, $values, $action) {
                        $form->add(
                            new \IPS\Helpers\Form\CheckboxSet(
                                'rules_Members_member_secondary_groups_add',
                                isset($values['rules_Members_member_secondary_groups_add']) ? $values['rules_Members_member_secondary_groups_add'] : null,
                                true,
                                [
                                    'options' => \IPS\Member\Group::groups(
                                        \IPS\Member::loggedIn()->hasAcpRestriction(
                                            'core',
                                            'members',
                                            'member_add_admin'
                                        ),
                                        false
                                    ),
                                    'multiple' => true,
                                    'parse' => 'normal',
                                ]
                            )
                        );
                    },
                ],
                'arguments' => [
                    'member' => [
                        'argtypes' => \IPS\rules\Application::argPreset('member'),
                        'configuration' => \IPS\rules\Application::configPreset('member', 'rules_choose_member'),
                        'required' => true,
                    ],
                ],
            ],
            'remove_secondary_groups' => [
                'callback' => [$this, 'removeSecondaryGroups'],
                'configuration' => [
                    'form' => function ($form, $values, $action) {
                        $form->add(
                            new \IPS\Helpers\Form\CheckboxSet(
                                'rules_Members_member_secondary_groups_remove',
                                isset($values['rules_Members_member_secondary_groups_remove']) ? $values['rules_Members_member_secondary_groups_remove'] : null,
                                true,
                                [
                                    'options' => \IPS\Member\Group::groups(
                                        \IPS\Member::loggedIn()->hasAcpRestriction(
                                            'core',
                                            'members',
                                            'member_add_admin'
                                        ),
                                        false
                                    ),
                                    'multiple' => true,
                                    'parse' => 'normal',
                                ]
                            )
                        );
                    },
                ],
                'arguments' => [
                    'member' => [
                        'argtypes' => \IPS\rules\Application::argPreset('member'),
                        'configuration' => \IPS\rules\Application::configPreset('member', 'rules_choose_member'),
                        'required' => true,
                    ],
                ],
            ],
            'set_secondary_groups' => [
                'callback' => [$this, 'setSecondaryGroups'],
                'configuration' => [
                    'form' => function ($form, $values, $action) {
                        $form->add(
                            new \IPS\Helpers\Form\CheckboxSet(
                                'rules_Members_member_secondary_groups_set',
                                isset($values['rules_Members_member_secondary_groups_set']) ? $values['rules_Members_member_secondary_groups_set'] : null,
                                true,
                                [
                                    'options' => \IPS\Member\Group::groups(
                                        \IPS\Member::loggedIn()->hasAcpRestriction(
                                            'core',
                                            'members',
                                            'member_add_admin'
                                        ),
                                        false
                                    ),
                                    'multiple' => true,
                                    'parse' => 'normal',
                                ]
                            )
                        );
                    },
                ],
                'arguments' => [
                    'member' => [
                        'argtypes' => \IPS\rules\Application::argPreset('member'),
                        'configuration' => \IPS\rules\Application::configPreset('member', 'rules_choose_member'),
                        'required' => true,
                    ],
                ],
            ],
            'change_member_title' => [
                'callback' => [$this, 'changeMemberTitle'],
                'arguments' => [
                    'member' => [
                        'argtypes' => \IPS\rules\Application::argPreset('member'),
                        'configuration' => \IPS\rules\Application::configPreset('member', 'rules_choose_member'),
                        'required' => true,
                    ],
                    'title' => [
                        'configuration' => [
                            'form' => function ($form, $values, $action) {
                                $form->add(
                                    new \IPS\Helpers\Form\Text(
                                        'rules_Members_member_title',
                                        isset($values['rules_Members_member_title']) ? $values['rules_Members_member_title'] : null,
                                        true,
                                        [],
                                        null,
                                        null,
                                        null,
                                        'rules_Members_member_title'
                                    )
                                );
                                return ['rules_Members_member_title'];
                            },
                            'getArg' => function ($values, $action) {
                                return $values['rules_Members_member_title'];
                            },
                        ],
                        'argtypes' => ['string'],
                        'required' => true,
                    ],
                ],
            ],
            'flag_spammer' => [
                'callback' => [$this, 'flagAsSpammer'],
                'arguments' => [
                    'member' => [
                        'argtypes' => \IPS\rules\Application::argPreset('member'),
                        'configuration' => \IPS\rules\Application::configPreset('member', 'rules_choose_member'),
                        'required' => true,
                    ],
                ],
            ],
            'unflag_spammer' => [
                'callback' => [$this, 'unflagAsSpammer'],
                'arguments' => [
                    'member' => [
                        'argtypes' => \IPS\rules\Application::argPreset('member'),
                        'configuration' => \IPS\rules\Application::configPreset('member', 'rules_choose_member'),
                        'required' => true,
                    ],
                ],
            ],
            'ban_member' => [
                'callback' => [$this, 'banMember'],
                'configuration' => [
                    'form' => function ($form, $values, $aciton) {
                        $ban_options = [
                            'permanent' => 'rules_ban_permanent',
                            'temporary' => 'rules_ban_temporary',
                        ];

                        $ban_toggles = [
                            'temporary' => [
                                'rules_Members_ban_setting_minutes',
                                'rules_Members_ban_setting_hours',
                                'rules_Members_ban_setting_days',
                                'rules_Members_ban_setting_months',
                            ],
                        ];

                        $form->add(
                            new \IPS\Helpers\Form\Radio(
                                'rules_Members_ban_setting',
                                isset($values['rules_Members_ban_setting']) ? $values['rules_Members_ban_setting'] : null,
                                true,
                                ['options' => $ban_options, 'toggles' => $ban_toggles]
                            )
                        );
                        $form->add(
                            new \IPS\Helpers\Form\Number(
                                'rules_Members_ban_setting_minutes',
                                isset($values['rules_Members_ban_setting_minutes']) ? $values['rules_Members_ban_setting_minutes'] : 0,
                                true,
                                [],
                                null,
                                null,
                                null,
                                'rules_Members_ban_setting_minutes'
                            )
                        );
                        $form->add(
                            new \IPS\Helpers\Form\Number(
                                'rules_Members_ban_setting_hours',
                                isset($values['rules_Members_ban_setting_hours']) ? $values['rules_Members_ban_setting_hours'] : 0,
                                true,
                                [],
                                null,
                                null,
                                null,
                                'rules_Members_ban_setting_hours'
                            )
                        );
                        $form->add(
                            new \IPS\Helpers\Form\Number(
                                'rules_Members_ban_setting_days',
                                isset($values['rules_Members_ban_setting_days']) ? $values['rules_Members_ban_setting_days'] : 0,
                                true,
                                [],
                                null,
                                null,
                                null,
                                'rules_Members_ban_setting_days'
                            )
                        );
                        $form->add(
                            new \IPS\Helpers\Form\Number(
                                'rules_Members_ban_setting_months',
                                isset($values['rules_Members_ban_setting_months']) ? $values['rules_Members_ban_setting_months'] : 0,
                                true,
                                [],
                                null,
                                null,
                                null,
                                'rules_Members_ban_setting_months'
                            )
                        );
                    },
                ],
                'arguments' => [
                    'member' => [
                        'argtypes' => \IPS\rules\Application::argPreset('member'),
                        'configuration' => \IPS\rules\Application::configPreset('member', 'rules_choose_member'),
                        'required' => true,
                    ],
                ],
            ],
            'unban_member' => [
                'callback' => [$this, 'unbanMember'],
                'arguments' => [
                    'member' => [
                        'argtypes' => \IPS\rules\Application::argPreset('member'),
                        'configuration' => \IPS\rules\Application::configPreset('member', 'rules_choose_member'),
                        'required' => true,
                    ],
                ],
            ],
            'prune_member' => [
                'callback' => [$this, 'pruneMember'],
                'arguments' => [
                    'member' => [
                        'argtypes' => \IPS\rules\Application::argPreset('member'),
                        'configuration' => \IPS\rules\Application::configPreset('member', 'rules_choose_member'),
                        'required' => true,
                    ],
                ],
            ],
        ];

        return $actions;
    }

    /***            ***
     ***     CONDITIONS    ***
     ***            ***/

    /**
     * Check a member
     *
     * @return    bool
     */
    public function checkMember($member, $values)
    {
        $members = (array)$values['rules_choose_members'];

        if (!$member instanceof \IPS\Member) {
            return false;
        }

        return in_array($member->member_id, $members);
    }


    /**
     * Check Member Groups
     */
    public function checkMemberGroup($member, $values)
    {
        if (!$member instanceof \IPS\Member) {
            return false;
        }

        return $member->inGroup($values['rules_Members_member_groups']);
    }

    /**
     * Check Member Attributes
     */
    public function checkMemberAttributes($member, $values)
    {
        if (!$member instanceof \IPS\Member) {
            return false;
        }

        switch ($values['rules_Members_member_attribute']) {
            case 'photo':
                return (bool)$member->pp_main_photo;

            case 'signature':
                return (bool)$member->signature;

            case 'followers':

                $amount = count($member->followers());
                break;

            case 'reputation':

                $amount = $member->pp_reputation_points;
                break;

            case 'posts':

                $amount = $member->real_member_posts;
                break;

            case 'pviews':

                $amount = $member->members_profile_views;
                break;

            case 'joined':

                $date = $member->joined;
                break;

            case 'birthdate':

                if (!$member->bday_year) {
                    return false;
                }

                $date = new \IPS\DateTime($member->bday_year . '/' . $member->bday_month . '/' . $member->bday_day);
                break;

            case 'last_activity':

                $date = \IPS\DateTime::ts($member->last_activity);
                break;

            case 'last_post':

                $date = \IPS\DateTime::ts($member->member_last_post);
                break;
        }

        switch ($values['rules_Members_member_attribute']) {
            case 'followers':
            case 'reputation':
            case 'posts':
            case 'pviews':

                $value = $values['rules_Members_attribute_compare_value'];
                switch ($values['rules_Members_attribute_compare_type_value']) {
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

            case 'joined':
            case 'birthdate':
            case 'last_activity':
            case 'last_post':

                $value = $values['rules_Members_attribute_compare_date'];
                switch ($values['rules_Members_attribute_compare_type_date']) {
                    case '?':
                        $value = strtotime(
                            '-' . intval($values['rules_Members_attribute_compare_minutes']) . ' minutes ' .
                            '-' . intval($values['rules_Members_attribute_compare_hours']) . ' hours ' .
                            '-' . intval($values['rules_Members_attribute_compare_days']) . ' days ' .
                            '-' . intval($values['rules_Members_attribute_compare_months']) . ' months ' .
                            '-' . intval($values['rules_Members_attribute_compare_years']) . ' years '
                        );
                        return $date->getTimestamp() > $value;

                    case '>':
                        return $date->getTimestamp() > $value;

                    case '<':
                        return $date->getTimestamp() < $value;

                    case '=':
                        $value = \IPS\DateTime::ts($value);
                        return (
                            $value->format('Y') == $date->format('Y') and
                            $value->format('m') == $date->format('m') and
                            $value->format('d') == $date->format('d')
                        );
                }
        }

        return false;
    }

    /**
     * Check Member Status
     */
    public function checkMemberStatus($member, $values)
    {
        if ($member instanceof \IPS\Member) {
            switch ($values['rules_Members_member_status']) {
                case 'validating':
                    return $member->members_bitoptions['validating'];

                case 'spammer':
                    return $member->members_bitoptions['bw_is_spammer'];

                case 'banned_perm':
                    return $member->temp_ban == -1;

                case 'banned_temp':
                    return $member->temp_ban > 0;

                case 'warnlevel':
                    return $member->warn_level > 0;

                case 'online':
                    return $member->isOnline();

                default:
                    return false;
            }
        }

        return false;
    }

    /**
     * Member Following Another Member
     */
    public function memberFollowing($member, $member2)
    {
        if (!($member instanceof \IPS\Member) or !($member2 instanceof \IPS\Member)) {
            return false;
        }

        try {
            $where = [
                [
                    'follow_app=? AND follow_area=? AND follow_member_id=? AND follow_rel_id=?',
                    'core',
                    'member',
                    $member->member_id,
                    $member2->member_id,
                ],
            ];
            \IPS\Db::i()->select('core_follow.*', 'core_follow', $where)->first();
            return true;
        } catch (\UnderflowException $e) {
            return false;
        }
    }

    /**
     * Member Ignoring Another Member
     */
    public function memberIgnoring($member, $member2, $values)
    {
        if (!($member instanceof \IPS\Member) or !($member2 instanceof \IPS\Member)) {
            return false;
        }

        if (!$member2 instanceof \IPS\Member) {
            return false;
        }

        return $member->isIgnoring($member2, $values['rules_Members_ignore_type']);
    }

    /***        ***
     ***  ACTIONS    ***
     ***        ***/

    /**
     * Change Member Primary Group
     */
    public function changePrimaryGroup($member, $values)
    {
        if ($member instanceof \IPS\Member) {
            if ($member->member_group_id != $values['rules_Members_member_primary_group']) {
                try {
                    $group = \IPS\Member\Group::load($values['rules_Members_member_primary_group']);
                    $member->member_group_id = $group->g_id;
                    $member->save();
                    return "member group changed";
                } catch (\OutOfRangeException $e) {
                    throw new \UnexpectedValueException("invalid member group, group not changed");
                }
            } else {
                return "member already has primary group";
            }
        } else {
            throw new \UnexpectedValueException("invalid member");
        }
    }

    /**
     * Add Member Secondary Groups
     */
    public function addSecondaryGroups($member, $values)
    {
        if ($member instanceof \IPS\Member) {
            $member_groups = explode(',', $member->mgroup_others);
            foreach ((array)$values['rules_Members_member_secondary_groups_add'] as $g_id) {
                try {
                    $group = \IPS\Member\Group::load($g_id);
                    $member_groups[] = $group->g_id;
                } catch (\OutOfRangeException $e) {
                }
            }

            $member_groups = array_unique($member_groups);
            $member->mgroup_others = implode(',', $member_groups);
            $member->save();
            return "member groups added";
        } else {
            throw new \UnexpectedValueException("invalid member");
        }
    }

    /**
     * Add Member Secondary Groups
     */
    public function removeSecondaryGroups($member, $values)
    {
        if ($member instanceof \IPS\Member) {
            $member_groups = explode(',', $member->mgroup_others);
            foreach ((array)$values['rules_Members_member_secondary_groups_remove'] as $g_id) {
                $i = array_search($g_id, $member_groups);
                if ($i !== false) {
                    unset($member_groups[$i]);
                }
            }

            $member->mgroup_others = implode(',', $member_groups);
            $member->save();
            return "member groups removed";
        } else {
            throw new \UnexpectedValueException("invalid member");
        }
    }

    /**
     * Add Member Secondary Groups
     */
    public function setSecondaryGroups($member, $values)
    {
        if ($member instanceof \IPS\Member) {
            $member_groups = [];
            foreach ((array)$values['rules_Members_member_secondary_groups_set'] as $g_id) {
                try {
                    $group = \IPS\Member\Group::load($g_id);
                    $member_groups[] = $group->g_id;
                } catch (\OutOfRangeException $e) {
                }
            }

            $member->mgroup_others = implode(',', $member_groups);
            $member->save();
            return "member groups set";
        } else {
            throw new \UnexpectedValueException("invalid member");
        }
    }

    /**
     * Change Member Title
     */
    public function changeMemberTitle($member, $title, $values)
    {
        if ($member instanceof \IPS\Member) {
            $member->member_title = $title;
            $member->save();
            return "member title changed";
        } else {
            throw new \UnexpectedValueException("invalid member");
        }
    }

    /**
     * Flag As Spammer Callback
     */
    public function flagAsSpammer($member)
    {
        if ($member instanceof \IPS\Member) {
            $member->flagAsSpammer();
            return "member flagged as spammer";
        } else {
            throw new \UnexpectedValueException("invalid member");
        }
    }

    /**
     * Unflag As Spammer Callback
     */
    public function unflagAsSpammer($member)
    {
        if ($member instanceof \IPS\Member) {
            $member->unflagAsSpammer();
            return "member unflagged as spammer";
        } else {
            throw new \UnexpectedValueException("invalid member");
        }
    }

    /**
     * Flag As Spammer Callback
     */
    public function banMember($member, $values)
    {
        if ($member instanceof \IPS\Member) {
            switch ($values['rules_Members_ban_setting']) {
                case 'temporary':
                    $ban_time = \strtotime
                    (
                        '+' . intval($values['rules_Members_ban_setting_months']) . ' months ' .
                        '+' . intval($values['rules_Members_ban_setting_days']) . ' days ' .
                        '+' . intval($values['rules_Members_ban_setting_hours']) . ' hours ' .
                        '+' . intval($values['rules_Members_ban_setting_minutes']) . ' minutes '
                    );
                    $member->temp_ban = $ban_time;
                    $member->save();
                    return "member temporarily banned";

                default:
                    $member->temp_ban = -1;
                    $member->save();
                    return "member banned permanently";
            }
        } else {
            throw new \UnexpectedValueException("invalid member");
        }
    }

    /**
     * Flag As Spammer Callback
     */
    public function unbanMember($member)
    {
        if ($member instanceof \IPS\Member) {
            $member->temp_ban = 0;
            $member->save();
            return "member unbanned";
        } else {
            throw new \UnexpectedValueException("invalid member");
        }
    }

    /**
     * Prune A Member
     */
    public function pruneMember($member, $values)
    {
        if ($member instanceof \IPS\Member) {
            $member->delete();
            return "member deleted";
        } else {
            throw new \UnexpectedValueException("invalid member");
        }
    }

}