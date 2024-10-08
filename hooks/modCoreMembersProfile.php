//<?php

class rules_hook_modCoreMembersProfile extends _HOOK_CLASS_
{

    /**
     * Edit Profile
     *
     * @return    void
     */
    protected function edit()
    {
        try {
            /* Forward Compatibility with ( 4.1.9+ ) */
            if (method_exists($this, 'buildEditForm')) {
                return parent::edit();
            }

            /* Do we have permission? */
            if (!\IPS\Member::loggedIn()->modPermission('can_modify_profiles') and (\IPS\Member::loggedIn(
                    )->member_id !== $this->member->member_id or !$this->member->group['g_edit_profile'])) {
                \IPS\Output::i()->error('no_permission_edit_profile', '2S147/1', 403, '');
            }

            /* Build the form */
            $form = new \IPS\Helpers\Form;

            /* The basics */
            $form->addTab('profile_edit_basic_tab', 'user');
            $form->addHeader('profile_edit_basic_header');
            if (\IPS\Settings::i()->post_titlechange != -1 and (isset(
                        \IPS\Settings::i()->post_titlechange
                    ) and $this->member->member_posts >= \IPS\Settings::i()->post_titlechange)) {
                $form->add(
                    new \IPS\Helpers\Form\Text(
                        'member_title',
                        $this->member->member_title,
                        false,
                        ['maxLength' => 64]
                    )
                );
            }

            $form->add(
                new \IPS\Helpers\Form\Custom(
                    'bday',
                    [
                        'year' => $this->member->bday_year,
                        'month' => $this->member->bday_month,
                        'day' => $this->member->bday_day,
                    ],
                    false,
                    [
                        'getHtml' => function ($element) {
                            return strtr(\IPS\Member::loggedIn()->language()->preferredDateFormat(), [
                                'dd' => \IPS\Theme::i()->getTemplate('members', 'core', 'global')->bdayForm_day(
                                    $element->name,
                                    $element->value,
                                    $element->error
                                ),
                                'mm' => \IPS\Theme::i()->getTemplate('members', 'core', 'global')->bdayForm_month(
                                    $element->name,
                                    $element->value,
                                    $element->error
                                ),
                                'yy' => \IPS\Theme::i()->getTemplate('members', 'core', 'global')->bdayForm_year(
                                    $element->name,
                                    $element->value,
                                    $element->error
                                ),
                                'yyyy' => \IPS\Theme::i()->getTemplate('members', 'core', 'global')->bdayForm_year(
                                    $element->name,
                                    $element->value,
                                    $element->error
                                ),
                            ]);
                        },
                    ]
                )
            );
            if (\IPS\Settings::i()->profile_comments and $this->member->canAccessModule(
                    \IPS\Application\Module::get('core', 'status')
                )) {
                $form->add(
                    new \IPS\Helpers\Form\YesNo('enable_status_updates', $this->member->pp_setting_count_comments)
                );
            }

            /* Profile fields */
            try {
                $values = \IPS\Db::i()->select(
                    '*', 'core_pfields_content',
                    ['member_id=?', $this->member->member_id]
                )->first();
            } catch (\UnderflowException $e) {
                $values = [];
            }

            foreach (
                \IPS\core\ProfileFields\Field::fields(
                    $values,
                    \IPS\core\ProfileFields\PROFILE
                ) as $group => $fields
            ) {
                $form->addHeader("core_pfieldgroups_{$group}");
                foreach ($fields as $field) {
                    $form->add($field);
                }
            }

            /**
             * Rules Data Fields
             */
            foreach (
                \IPS\Db::i()->select(
                    '*', 'rules_data',
                    ['data_class=? AND data_use_mode IN ( \'public\', \'admin\' )', \IPS\Member::rulesDataClass()]
                ) as $row
            ) {
                if ($row['data_use_mode'] == 'public' or \IPS\Member::loggedIn()->modPermission(
                        'can_modify_profiles'
                    )) {
                    $data_field = \IPS\rules\Data::constructFromData($row);
                    if ($data_field->can('edit')) {
                        if (!isset($_rules_header)) {
                            $_rules_header = true and $form->addHeader("rules_profile_data_header");
                        }

                        foreach ($data_field->formElements($this->member) as $name => $element) {
                            $form->add($element);
                        }
                    }
                }
            }

            /* Moderator stuff */
            if (\IPS\Member::loggedIn()->modPermission('can_modify_profiles') and \IPS\Member::loggedIn(
                )->member_id != $this->member->member_id) {
                $form->add(
                    new \IPS\Helpers\Form\Editor(
                        'signature',
                        $this->member->signature,
                        false,
                        [
                            'app' => 'core',
                            'key' => 'Signatures',
                            'autoSaveKey' => "frontsig-" . $this->member->member_id,
                            'attachIds' => [$this->member->member_id],
                        ]
                    )
                );

                $form->addTab('profile_edit_moderation', 'times');

                if ($this->member->mod_posts !== 0) {
                    $form->add(new \IPS\Helpers\Form\YesNo('remove_mod_posts', null, false));
                }

                if ($this->member->restrict_post !== 0) {
                    $form->add(new \IPS\Helpers\Form\YesNo('remove_restrict_post', null, false));
                }

                if ($this->member->temp_ban !== 0) {
                    $form->add(new \IPS\Helpers\Form\YesNo('remove_ban', null, false));
                }
            }

            /* Handle the submission */
            if ($values = $form->values()) {
                if ((\IPS\Settings::i()->post_titlechange == -1 or (isset(
                                \IPS\Settings::i()->post_titlechange
                            ) and $this->member->member_posts >= \IPS\Settings::i(
                            )->post_titlechange)) and isset($values['member_title'])) {
                    $this->member->member_title = $values['member_title'];
                }

                if ($values['bday'] and (($values['bday']['day'] and !$values['bday']['month']) or ($values['bday']['month'] and !$values['bday']['day']))) {
                    $form->error = \IPS\Member::loggedIn()->language()->addToStack('bday_month_and_day_required');
                    \IPS\Output::i()->output = \IPS\Theme::i()->getTemplate('forms', 'core')->editContentForm(
                        \IPS\Member::loggedIn()->language()->addToStack('profile_edit'),
                        $form
                    );
                    return;
                }

                if ($values['bday'] and $values['bday']['day'] and $values['bday']['month']) {
                    $this->member->bday_day = $values['bday']['day'];
                    $this->member->bday_month = $values['bday']['month'];
                    $this->member->bday_year = $values['bday']['year'];
                } else {
                    $this->member->bday_day = null;
                    $this->member->bday_month = null;
                    $this->member->bday_year = null;
                }

                if (isset($values['enable_status_updates'])) {
                    $this->member->pp_setting_count_comments = $values['enable_status_updates'];

                    if ($values['enable_status_updates']) {
                        \IPS\Content\Search\Index::i()->massUpdate(
                            'IPS\core\Statuses\Status',
                            null,
                            null,
                            '*',
                            null,
                            null,
                            $this->member->member_id
                        );
                    } else {
                        \IPS\Content\Search\Index::i()->massUpdate(
                            'IPS\core\Statuses\Status',
                            null,
                            null,
                            '',
                            null,
                            null,
                            $this->member->member_id
                        );
                    }
                }

                /* Profile Fields */
                try {
                    $profileFields = \IPS\Db::i()->select(
                        '*', 'core_pfields_content',
                        ['member_id=?', $this->member->member_id]
                    )->first();
                } catch (\UnderflowException $e) {
                    $profileFields = [];
                }

                /* If the row only contains one column (eg. member_id) then the result of the query is a string, we do not want this */
                if (!is_array($profileFields)) {
                    $profileFields = [];
                }

                $profileFields['member_id'] = $this->member->member_id;

                foreach (
                    \IPS\core\ProfileFields\Field::fields(
                        $profileFields,
                        \IPS\core\ProfileFields\PROFILE
                    ) as $group => $fields
                ) {
                    foreach ($fields as $id => $field) {
                        $profileFields["field_{$id}"] = $field::stringValue($values[$field->name]);

                        if ($field instanceof \IPS\Helpers\Form\Editor) {
                            \IPS\core\ProfileFields\Field::load($id)->claimAttachments($this->member->member_id);
                        }
                    }

                    $this->member->changedCustomFields = $profileFields;
                }

                /**
                 * Save Custom Rules Data
                 */
                foreach (
                    \IPS\Db::i()->select(
                        '*', 'rules_data',
                        [
                            'data_class=? AND data_use_mode IN ( \'public\', \'admin\' )',
                            \IPS\Member::rulesDataClass(),
                        ]
                    ) as $row
                ) {
                    if (isset ($values['rules_data_' . $row['data_column_name']])) {
                        $this->member->setRulesData(
                            $row['data_column_name'],
                            $values['rules_data_' . $row['data_column_name']]
                        );
                        unset($values['rules_data_' . $row['data_column_name']]);
                    }
                }

                /* Moderator stuff */
                if (\IPS\Member::loggedIn()->modPermission('can_modify_profiles') and \IPS\Member::loggedIn(
                    )->member_id != $this->member->member_id) {
                    if (isset($values['remove_mod_posts']) and $values['remove_mod_posts']) {
                        $this->member->mod_posts = 0;
                    }

                    if (isset($values['remove_restrict_post']) and $values['remove_restrict_post']) {
                        $this->member->restrict_post = 0;
                    }

                    if (isset($values['remove_ban']) and $values['remove_ban']) {
                        $this->member->temp_ban = 0;
                    }

                    if (isset($values['signature'])) {
                        $this->member->signature = $values['signature'];
                    }
                }

                /* Save */
                \IPS\Db::i()->replace('core_pfields_content', $profileFields);
                $this->member->save();

                \IPS\Output::i()->redirect($this->member->url());
            }

            /* Set Session Location */
            \IPS\Session::i()->setLocation(
                $this->member->url(), [], 'loc_editing_profile',
                [$this->member->name => false]
            );

            if (\IPS\Request::i()->isAjax()) {
                \IPS\Output::i()->output = $form->customTemplate(
                    [
                        call_user_func_array([\IPS\Theme::i(), 'getTemplate'], ['forms', 'core']),
                        'popupTemplate',
                    ]
                );
            } else {
                \IPS\Output::i()->output = \IPS\Theme::i()->getTemplate('forms', 'core')->editContentForm(
                    \IPS\Member::loggedIn()->language()->addToStack('profile_edit'),
                    $form
                );
            }
            \IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack(
                'editing_profile', false,
                ['sprintf' => [$this->member->name]]
            );
            \IPS\Output::i()->breadcrumb[] = [
                null,
                \IPS\Member::loggedIn()->language()->addToStack(
                    'editing_profile', false,
                    ['sprintf' => [$this->member->name]]
                ),
            ];
        } catch (\RuntimeException $e) {
            if (method_exists(get_parent_class(), __FUNCTION__)) {
                return \call_user_func_array('parent::' . __FUNCTION__, \func_get_args());
            } else {
                throw $e;
            }
        }
    }

    /**
     * Build Edit Form ( IPS 4.1.9+ )
     *
     * @return \IPS\Helpers\Form
     */
    protected function buildEditForm()
    {
        try {
            $form = parent::buildEditForm();

            /**
             * Rules Data Fields
             */
            foreach (
                \IPS\Db::i()->select(
                    '*', 'rules_data',
                    ['data_class=? AND data_use_mode IN ( \'public\', \'admin\' )', \IPS\Member::rulesDataClass()]
                ) as $row
            ) {
                if ($row['data_use_mode'] == 'public' or \IPS\Member::loggedIn()->modPermission(
                        'can_modify_profiles'
                    )) {
                    $data_field = \IPS\rules\Data::constructFromData($row);
                    if ($data_field->can('edit')) {
                        if (!isset($_rules_header)) {
                            $_rules_header = true and $form->addHeader("rules_profile_data_header");
                        }

                        foreach ($data_field->formElements($this->member) as $name => $element) {
                            $form->add($element);
                        }
                    }
                }
            }

            return $form;
        } catch (\RuntimeException $e) {
            if (method_exists(get_parent_class(), __FUNCTION__)) {
                return \call_user_func_array('parent::' . __FUNCTION__, \func_get_args());
            } else {
                throw $e;
            }
        }
    }

    /**
     * Save Member ( IPS 4.1.9+ )
     *
     * @param $form
     * @param array $values
     */
    protected function _saveMember($form, array $values)
    {
        try {
            /**
             * Save Custom Rules Data
             */
            foreach (
                \IPS\Db::i()->select(
                    '*', 'rules_data',
                    ['data_class=? AND data_use_mode IN ( \'public\', \'admin\' )', \IPS\Member::rulesDataClass()]
                ) as $row
            ) {
                if (isset ($values['rules_data_' . $row['data_column_name']])) {
                    $this->member->setRulesData(
                        $row['data_column_name'],
                        $values['rules_data_' . $row['data_column_name']]
                    );
                    unset($values['rules_data_' . $row['data_column_name']]);
                }
            }

            return parent::_saveMember($form, $values);
        } catch (\RuntimeException $e) {
            if (method_exists(get_parent_class(), __FUNCTION__)) {
                return \call_user_func_array('parent::' . __FUNCTION__, \func_get_args());
            } else {
                throw $e;
            }
        }
    }


}