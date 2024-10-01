//<?php

class rules_hook_themCoreFrontProfile extends _HOOK_CLASS_
{

    /* !Hook Data - DO NOT REMOVE */
    public static function hookData()
    {
        return array_merge_recursive([
            'profile' =>
                [
                    0 =>
                        [
                            'selector' => '#elProfileInfoColumn > div',
                            'type' => 'add_inside_end',
                            'content' => '{template="memberDataDisplay" app="rules" group="components" params="$member"}',
                        ],
                ],
        ], parent::hookData());
    }
    /* End Hook Data */


}