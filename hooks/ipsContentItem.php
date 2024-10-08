//<?php

abstract class rules_hook_ipsContentItem extends _HOOK_CLASS_
{

    /**
     * Move
     *
     * @param \IPS\Node\Model $container Container to move to
     * @param bool $keepLink If TRUE, will keep a link in the source
     * @return    void
     */
    public function move(\IPS\Node\Model $container, $keepLink = false)
    {
        try {
            $oldContainer = $this->container();
            $result = call_user_func_array('parent::move', func_get_args());

            \IPS\rules\Event::load('rules', 'Content', 'content_item_moved')->trigger(
                $this,
                $oldContainer,
                $container,
                $keepLink
            );

            $classEvent = \IPS\rules\Event::load('rules', 'Content', 'content_item_moved_' . md5(get_class($this)));
            if (!$classEvent->placeholder) {
                $classEvent->trigger($this, $oldContainer, $container, $keepLink);
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
     * Merge other items in (they will be deleted, this will be kept)
     *
     * @param array $items Items to merge in
     * @return    void
     */
    public function mergeIn(array $items)
    {
        try {
            \IPS\rules\Event::load('rules', 'Content', 'content_item_merging')->trigger($this, $items);

            $classEvent = \IPS\rules\Event::load('rules', 'Content', 'content_item_merging_' . md5(get_class($this)));
            if (!$classEvent->placeholder) {
                $classEvent->trigger($this, $items);
            }

            return call_user_func_array('parent::mergeIn', func_get_args());
        } catch (\RuntimeException $e) {
            if (method_exists(get_parent_class(), __FUNCTION__)) {
                return \call_user_func_array('parent::' . __FUNCTION__, \func_get_args());
            } else {
                throw $e;
            }
        }
    }

    /**
     * Publishes a 'future' entry now
     *
     * @param \IPS\Member|NULL $member The member doing the action (NULL for currently logged in member)
     * @return    void
     */
    public function publish($member = null)
    {
        try {
            $result = call_user_func_array('parent::publish', func_get_args());

            \IPS\rules\Event::load('rules', 'Content', 'content_item_published')->trigger(
                $this,
                $member ?: \IPS\Member::loggedIn()
            );

            $classEvent = \IPS\rules\Event::load('rules', 'Content', 'content_item_published_' . md5(get_class($this)));
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
     * "Unpublishes" an item.
     * @note    This will not change the item's date. This should be done via the form methods if required
     *
     * @param \IPS\Member|NULL $member The member doing the action (NULL for currently logged in member)
     * @return    void
     */
    public function unpublish($member = null)
    {
        try {
            $result = call_user_func_array('parent::unpublish', func_get_args());

            \IPS\rules\Event::load('rules', 'Content', 'content_item_unpublished')->trigger(
                $this,
                $member ?: \IPS\Member::loggedIn()
            );

            $classEvent = \IPS\rules\Event::load(
                'rules',
                'Content',
                'content_item_unpublished_' . md5(get_class($this))
            );
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
     * Set tags
     *
     * @param array $set The tags (if one has the key "prefix", it will be set as the prefix)
     * @return    void
     */
    public function setTags($set)
    {
        try {
            $result = call_user_func_array('parent::setTags', func_get_args());

            \IPS\rules\Event::load('rules', 'Content', 'content_item_tags_set')->trigger($this, $set);

            $classEvent = \IPS\rules\Event::load('rules', 'Content', 'content_item_tags_set_' . md5(get_class($this)));
            if (!$classEvent->placeholder) {
                $classEvent->trigger($this, $set);
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
     * Get elements for add/edit form
     *
     * @param \IPS\Content\Item|NULL $item The current item if editing or NULL if creating
     * @param \IPS\Node\Model|NULL $container Container (e.g. forum), if appropriate
     * @return    array
     */
    public static function formElements($item = null, \IPS\Node\Model $container = null)
    {
        try {
            $formElements = parent::formElements($item, $container);
            $rulesFormElements = static::rulesFormElements($item, $container);

            $pollPosition = array_search('poll', array_keys($formElements));

            if ($pollPosition !== false) {
                $formElements = array_merge(
                    array_slice($formElements, 0, $pollPosition),
                    $rulesFormElements,
                    array_slice($formElements, $pollPosition)
                );
            } else {
                $formElements = array_merge($formElements, $rulesFormElements);
            }

            return $formElements;
        } catch (\RuntimeException $e) {
            if (method_exists(get_parent_class(), __FUNCTION__)) {
                return \call_user_func_array('parent::' . __FUNCTION__, \func_get_args());
            } else {
                throw $e;
            }
        }
    }

    /**
     * Get elements for add/edit form
     *
     * @param \IPS\Content\Item|NULL $item The current item if editing or NULL if creating
     * @param \IPS\Node\Model|NULL $container Container (e.g. forum), if appropriate
     * @return    array
     */
    public static function rulesFormElements($item = null, \IPS\Node\Model $container = null)
    {
        try {
            $formElements = [];

            foreach (
                \IPS\Db::i()->select(
                    '*', 'rules_data',
                    ['data_class=? AND data_use_mode IN ( \'public\', \'admin\' )', static::rulesDataClass()]
                ) as $row
            ) {
                if ($row['data_use_mode'] == 'public' or static::modPermission('edit', null, $container)) {
                    $data_field = \IPS\rules\Data::constructFromData($row);

                    /**
                     * Check if this data only applies in specific containers
                     */
                    if (isset(static::$containerNodeClass) and $nodeClass = static::$containerNodeClass) {
                        $configuration = json_decode($data_field->configuration, true) ?: [];
                        $containers = 'containers-' . str_replace('\\', '-', $nodeClass);

                        if (isset($configuration[$containers]) and is_array($configuration[$containers])) {
                            $node_id = 0;
                            if (isset($container)) {
                                $node_id = $container->_id;
                            }

                            if (!in_array($node_id, $configuration[$containers])) {
                                continue;
                            }
                        }
                    }

                    /**
                     * Check if user has permission to edit field
                     */
                    if ($data_field->can('edit')) {
                        $formElements = array_merge($formElements, $data_field->formElements($item));
                    }
                }
            }

            return $formElements;
        } catch (\RuntimeException $e) {
            if (method_exists(get_parent_class(), __FUNCTION__)) {
                return \call_user_func_array('parent::' . __FUNCTION__, \func_get_args());
            } else {
                throw $e;
            }
        }
    }

    /**
     * Process create/edit form
     *
     * @param array $values Values from form
     * @return    void
     */
    public function processForm($values)
    {
        try {
            parent::processForm($values);

            /**
             * Support for IPS 4.4+ where they now assume that the content has not been saved in a parent method to
             * perform their content creation correctly.
             *
             * @see: ./applications/core/sources/Statuses/Status.php : 421
             */
            if ($this instanceof \IPS\core\Statuses\Status and $this->_new) {
                return;
            }

            /**
             * Make sure we have an ID
             */
            $idField = static::$databaseColumnId;
            if (!$this->$idField) {
                $this->save();
            }

            /**
             * We can only update rules data after this item has been saved to the database
             */
            foreach (
                \IPS\Db::i()->select(
                    '*', 'rules_data',
                    ['data_class=? AND data_use_mode IN ( \'public\', \'admin\' )', static::rulesDataClass()]
                ) as $row
            ) {
                $data_field = \IPS\rules\Data::constructFromData($row);

                if (isset ($values['rules_data_' . $data_field->column_name])) {
                    $this->setRulesData($data_field->column_name, $data_field->valueFromForm($values));
                }
            }

            /**
             * If rules data has changed, save yet again.
             */
            if ($this->rulesDataChanged) {
                $this->save();
            }
        } catch (\RuntimeException $e) {
            if (method_exists(get_parent_class(), __FUNCTION__)) {
                return \call_user_func_array('parent::' . __FUNCTION__, \func_get_args());
            } else {
                throw $e;
            }
        }
    }

}