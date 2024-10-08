//<?php

abstract class rules_hook_ipsContent extends _HOOK_CLASS_
{

    /**
     * Hide
     *
     * @param \IPS\Member|NULL|FALSE $member The member doing the action (NULL for currently logged in member, FALSE for no member)
     * @param string $reason Reason
     * @return    void
     */
    public function hide($member, $reason = null)
    {
        try {
            $result = call_user_func_array('parent::hide', func_get_args());

            \IPS\rules\Event::load('rules', 'Content', 'content_hidden')->trigger(
                $this,
                $member ?: \IPS\Member::loggedIn(),
                $reason
            );

            $classEvent = \IPS\rules\Event::load('rules', 'Content', 'content_hidden_' . md5(get_class($this)));
            if (!$classEvent->placeholder) {
                $classEvent->trigger($this, $member ?: \IPS\Member::loggedIn(), $reason);
            }

            return $result;
        } catch (\RuntimeException $e) {
            if (method_exists(get_parent_class(), __FUNCTION__)) {
                return \call_user_func_array('parent::' . __FUNCTION__, \func_get_args());
            } else {
                throw $e;
            }
        }
    }

    /**
     * Unhide
     *
     * @param \IPS\Member|NULL $member The member doing the action (NULL for currently logged in member)
     * @return    void
     */
    public function unhide($member)
    {
        try {
            /* If we're approving, we have to do extra stuff */
            $approving = $this->hidden() === 1 ? true : false;

            $result = call_user_func_array('parent::unhide', func_get_args());

            if ($approving) {
                \IPS\rules\Event::load('rules', 'Content', 'content_approved')->trigger($this, $member);
                $approvingEvent = \IPS\rules\Event::load(
                    'rules',
                    'Content',
                    'content_approved_' . md5(get_class($this))
                );
                if (!$approvingEvent->placeholder) {
                    $approvingEvent->trigger($this, $member);
                }
            }

            \IPS\rules\Event::load('rules', 'Content', 'content_unhidden')->trigger(
                $this,
                $member ?: \IPS\Member::loggedIn()
            );

            $classEvent = \IPS\rules\Event::load('rules', 'Content', 'content_unhidden_' . md5(get_class($this)));
            if (!$classEvent->placeholder) {
                $classEvent->trigger($this, $member ?: \IPS\Member::loggedIn());
            }

            return $result;
        } catch (\RuntimeException $e) {
            if (method_exists(get_parent_class(), __FUNCTION__)) {
                return \call_user_func_array('parent::' . __FUNCTION__, \func_get_args());
            } else {
                throw $e;
            }
        }
    }

    /**
     * Change Author
     *
     * @param \IPS\Member $newAuthor The new author
     * @return    void
     */
    public function changeAuthor(\IPS\Member $newAuthor)
    {
        try {
            $oldAuthor = $this->author();
            $result = call_user_func_array('parent::changeAuthor', func_get_args());

            \IPS\rules\Event::load('rules', 'Content', 'content_author_changed')->trigger(
                $this,
                $oldAuthor,
                $newAuthor
            );

            $classEvent = \IPS\rules\Event::load('rules', 'Content', 'content_author_changed_' . md5(get_class($this)));
            if (!$classEvent->placeholder) {
                $classEvent->trigger($this, $oldAuthor, $newAuthor);
            }

            return $result;
        } catch (\RuntimeException $e) {
            if (method_exists(get_parent_class(), __FUNCTION__)) {
                return \call_user_func_array('parent::' . __FUNCTION__, \func_get_args());
            } else {
                throw $e;
            }
        }
    }

    /**
     * Report
     *
     * @param string $reportContent Report content message from member
     * @return    \\IPS\core\Reports\Report
     * @throws    \UnexpectedValueException    If there is a permission error - you should only call this method after checking canReport
     */
    public function report($reportContent)
    {
        try {
            $result = call_user_func_array('parent::report', func_get_args());

            \IPS\rules\Event::load('rules', 'Content', 'content_reported')->trigger($this, $reportContent);

            $classEvent = \IPS\rules\Event::load('rules', 'Content', 'content_reported_' . md5(get_class($this)));
            if (!$classEvent->placeholder) {
                $classEvent->trigger($this, $reportContent);
            }

            return $result;
        } catch (\RuntimeException $e) {
            if (method_exists(get_parent_class(), __FUNCTION__)) {
                return \call_user_func_array('parent::' . __FUNCTION__, \func_get_args());
            } else {
                throw $e;
            }
        }
    }

    /**
     * Give reputation
     *
     * @param int $type 1 for positive, -1 for negative
     * @param \IPS\Member|NULL $member The member to check for (NULL for currently logged in member)
     * @return    void
     * @throws    \DomainException|\BadMethodCallException
     */
    public function giveReputation($type, \IPS\Member $member = null)
    {
        try {
            $result = call_user_func_array('parent::giveReputation', func_get_args());

            \IPS\rules\Event::load('rules', 'Members', 'reputation_given')->trigger(
                $this->author(),
                $member ?: \IPS\Member::loggedIn(),
                $this,
                $type
            );

            return $result;
        } catch (\RuntimeException $e) {
            if (method_exists(get_parent_class(), __FUNCTION__)) {
                return \call_user_func_array('parent::' . __FUNCTION__, \func_get_args());
            } else {
                throw $e;
            }
        }
    }

    /**
     * Do Moderator Action
     *
     * @param string $action The action
     * @param \IPS\Member|NULL $member The member doing the action (NULL for currently logged in member)
     * @param string|NULL $reason Reason (for hides)
     * @return    void
     * @throws    \OutOfRangeException|\InvalidArgumentException|\RuntimeException
     */
    public function modAction($action, \IPS\Member $member = null, $reason = null)
    {
        try {
            $result = call_user_func_array('parent::modAction', func_get_args());
            $member = $member ?: \IPS\Member::loggedIn();

            $this->modActionEvent($action, $member);

            return $result;
        } catch (\RuntimeException $e) {
            if (method_exists(get_parent_class(), __FUNCTION__)) {
                return \call_user_func_array('parent::' . __FUNCTION__, \func_get_args());
            } else {
                throw $e;
            }
        }
    }

    /**
     * Trigger moderation action events
     */
    public function modActionEvent($action, $member = null)
    {
        try {
            $member = $member ?: new \IPS\Member;

            switch ($action) {
                case 'pin'    :
                    \IPS\rules\Event::load('rules', 'Content', 'content_pinned')->trigger($this, $member);
                    break;
                case 'unpin'    :
                    \IPS\rules\Event::load('rules', 'Content', 'content_unpinned')->trigger($this, $member);
                    break;
                case 'feature'    :
                    \IPS\rules\Event::load('rules', 'Content', 'content_featured')->trigger($this, $member);
                    break;
                case 'unfeature':
                    \IPS\rules\Event::load('rules', 'Content', 'content_unfeatured')->trigger($this, $member);
                    break;
                case 'lock'    :
                    \IPS\rules\Event::load('rules', 'Content', 'content_locked')->trigger($this, $member);
                    break;
                case 'unlock'    :
                    \IPS\rules\Event::load('rules', 'Content', 'content_unlocked')->trigger($this, $member);
                    break;
            }

            switch ($action) {
                case 'pin'    :
                    $classEvent = \IPS\rules\Event::load('rules', 'Content', 'content_pinned_' . md5(get_class($this)));
                    break;
                case 'unpin'    :
                    $classEvent = \IPS\rules\Event::load(
                        'rules',
                        'Content',
                        'content_unpinned_' . md5(get_class($this))
                    );
                    break;
                case 'feature'    :
                    $classEvent = \IPS\rules\Event::load(
                        'rules',
                        'Content',
                        'content_featured_' . md5(get_class($this))
                    );
                    break;
                case 'unfeature':
                    $classEvent = \IPS\rules\Event::load(
                        'rules',
                        'Content',
                        'content_unfeatured_' . md5(get_class($this))
                    );
                    break;
                case 'lock'    :
                    $classEvent = \IPS\rules\Event::load('rules', 'Content', 'content_locked_' . md5(get_class($this)));
                    break;
                case 'unlock'    :
                    $classEvent = \IPS\rules\Event::load(
                        'rules',
                        'Content',
                        'content_unlocked_' . md5(get_class($this))
                    );
                    break;
            }

            if (isset($classEvent) and !$classEvent->placeholder) {
                $classEvent->trigger($this, $member);
            }
        } catch (\RuntimeException $e) {
            if (method_exists(get_parent_class(), __FUNCTION__)) {
                return \call_user_func_array('parent::' . __FUNCTION__, \func_get_args());
            } else {
                throw $e;
            }
        }
    }

    /**
     * Save Changed Columns
     *
     * @return    void
     */
    public function save()
    {
        try {
            if ($this->_new) {
                $result = call_user_func_array('parent::save', func_get_args());

                \IPS\rules\Event::load('rules', 'Content', 'content_created')->trigger($this);
                \IPS\rules\Event::load('rules', 'Content', 'content_updated')->trigger($this, $this->_data, true);

                $createdEvent = \IPS\rules\Event::load('rules', 'Content', 'content_created_' . md5(get_class($this)));
                if (!$createdEvent->placeholder) {
                    $createdEvent->trigger($this);
                }

                $updatedEvent = \IPS\rules\Event::load('rules', 'Content', 'content_updated_' . md5(get_class($this)));
                if (!$updatedEvent->placeholder) {
                    $updatedEvent->trigger($this, $this->_data, true);
                }
            } else {
                $changed = $this->changed;
                $rulesDataChanged = $this->rulesDataChanged;

                $result = call_user_func_array('parent::save', func_get_args());

                if (!empty($changed) or $rulesDataChanged) {
                    \IPS\rules\Event::load('rules', 'Content', 'content_updated')->trigger($this, $changed, false);

                    $updatedEvent = \IPS\rules\Event::load(
                        'rules',
                        'Content',
                        'content_updated_' . md5(get_class($this))
                    );
                    if (!$updatedEvent->placeholder) {
                        $updatedEvent->trigger($this, $changed, false);
                    }
                }
            }

            return $result;
        } catch (\RuntimeException $e) {
            if (method_exists(get_parent_class(), __FUNCTION__)) {
                return \call_user_func_array('parent::' . __FUNCTION__, \func_get_args());
            } else {
                throw $e;
            }
        }
    }

    /**
     * Delete Record
     *
     * @return    void
     */
    public function delete()
    {
        try {
            $result = call_user_func_array('parent::delete', func_get_args());

            \IPS\rules\Event::load('rules', 'Content', 'content_deleted')->trigger($this);

            $classEvent = \IPS\rules\Event::load('rules', 'Content', 'content_deleted_' . md5(get_class($this)));
            if (!$classEvent->placeholder) {
                $classEvent->trigger($this);
            }

            return $result;
        } catch (\RuntimeException $e) {
            if (method_exists(get_parent_class(), __FUNCTION__)) {
                return \call_user_func_array('parent::' . __FUNCTION__, \func_get_args());
            } else {
                throw $e;
            }
        }
    }

    /**
     * Returns the content
     *
     * @return    string
     */
    public function content()
    {
        try {
            /* An exception is thrown if trying to access dispatcher instance in command line mode */
            try {
                if (\IPS\Request::i()->do !== 'edit' and \IPS\Dispatcher::i()->controllerLocation == 'front') {
                    if (is_subclass_of(get_called_class(), '\IPS\Content\Comment')) {
                        /* Add in content if the item does not support its own content and this is the first comment */
                        if ($item = $this->item() and !$item::$databaseColumnMap['content'] and $item->rulesDataFields(
                            )) {
                            if (isset($item::$databaseColumnMap['first_comment_id'])) {
                                $firstCommentIdColumn = $item::$databaseColumnMap['first_comment_id'];
                                if ($item->$firstCommentIdColumn == $this->activeid) {
                                    return \IPS\Theme::i()->getTemplate(
                                        'components',
                                        'rules',
                                        'front'
                                    )->contentDataDisplay($item, parent::content());
                                }
                            }
                        }
                    }

                    if (is_subclass_of(
                            get_called_class(),
                            '\IPS\Content\Item'
                        ) and static::$databaseColumnMap['content']) {
                        if ($this->rulesDataFields()) {
                            return \IPS\Theme::i()->getTemplate('components', 'rules', 'front')->contentDataDisplay(
                                $this,
                                parent::content()
                            );
                        }
                    }
                }
            } catch (\Exception $e) {
            }

            return parent::content();
        } catch (\RuntimeException $e) {
            if (method_exists(get_parent_class(), __FUNCTION__)) {
                return \call_user_func_array('parent::' . __FUNCTION__, \func_get_args());
            } else {
                throw $e;
            }
        }
    }


}