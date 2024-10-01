//<?php

class rules_hook_ipsCoreWarningsWarning extends _HOOK_CLASS_
{

    /**
     * Process created object AFTER the object has been created
     *
     * @param \IPS\Content\Comment|NULL $comment The first comment
     * @param array $values Values from form
     * @return    void
     */
    protected function processAfterCreate($comment, $values)
    {
        try {
            $result = call_user_func_array('parent::processAfterCreate', func_get_args());
            \IPS\rules\Event::load('rules', 'Members', 'member_warned')->trigger(
                $this,
                \IPS\Member::load($this->member),
                $this->author()
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

}