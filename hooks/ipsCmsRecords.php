//<?php

class rules_hook_ipsCmsRecords extends _HOOK_CLASS_
{

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
            /**
             * This code is also in our hook for \IPS\Content\Item, but the pages app neglects our added elements
             * so we're forced to hook here again.
             */
            return array_merge(parent::formElements($item, $container), static::rulesFormElements($item, $container));
        } catch (\RuntimeException $e) {
            if (method_exists(get_parent_class(), __FUNCTION__)) {
                return \call_user_func_array('parent::' . __FUNCTION__, \func_get_args());
            } else {
                throw $e;
            }
        }
    }

    /**
     * Get available comment/review tabs
     *
     * @return    array
     */
    public function commentReviewTabs()
    {
        try {
            $tabs = parent::commentReviewTabs();

            foreach (
                \IPS\rules\Log\Custom::roots(
                    'view',
                    null,
                    array(array('custom_log_class=? AND custom_log_enabled=1', $this::rulesDataClass()))
                ) as $log
            ) {
                if ($log->display_empty or $log->logCount($this)) {
                    $tab_id = 'custom_log_' . $log->id;
                    $tabs[$tab_id] = $log->title;
                }
            }

            return $tabs;
        } catch (\RuntimeException $e) {
            if (method_exists(get_parent_class(), __FUNCTION__)) {
                return \call_user_func_array('parent::' . __FUNCTION__, \func_get_args());
            } else {
                throw $e;
            }
        }
    }

    /**
     * Get comment/review output
     *
     * @param string $tab Active tab
     * @return    string
     */
    public function commentReviews($tab)
    {
        try {
            foreach (
                \IPS\rules\Log\Custom::roots(
                    'view',
                    null,
                    array(array('custom_log_class=? AND custom_log_enabled=1', $this::rulesDataClass()))
                ) as $log
            ) {
                if ($tab == 'custom_log_' . $log->id) {
                    return (string)$log->logsTable($this, $limit);
                }
            }

            return parent::commentReviews($tab);
        } catch (\RuntimeException $e) {
            if (method_exists(get_parent_class(), __FUNCTION__)) {
                return \call_user_func_array('parent::' . __FUNCTION__, \func_get_args());
            } else {
                throw $e;
            }
        }
    }

}