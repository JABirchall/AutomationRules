//<?php

abstract class rules_hook_ipsHelpersFormAbstract extends _HOOK_CLASS_
{

    /**
     * Force Validation Pass
     */
    public function noError()
    {
        try {
            $this->error = false;
            $this->valueSet = true;
        } catch (\RuntimeException $e) {
            if (method_exists(get_parent_class(), __FUNCTION__)) {
                return \call_user_func_array('parent::' . __FUNCTION__, \func_get_args());
            } else {
                throw $e;
            }
        }
    }

}