//<?php

/* To prevent PHP errors (extending class does not exist) revealing path */
if (!defined('\IPS\SUITE_UNIQUE_KEY')) {
    exit;
}

class rules_hook_ipsDispatcherApi extends _HOOK_CLASS_
{

    /**
     * Run
     *
     * @return    void
     */
    public function run()
    {
        try {
            /* Attempt to authorize a GitHub Signature */
            if (isset($_SERVER['HTTP_X_HUB_SIGNATURE'])) {
                list($algo, $hash) = explode('=', $_SERVER['HTTP_X_HUB_SIGNATURE'], 2) + ['', ''];
                if (!in_array($algo, hash_algos())) {
                    /* Output */
                    \IPS\Output::i()->sendOutput(
                        json_encode(['errorCode' => 'EX2S291/8', 'errorMessage' => 'HASHING UNAVAILABLE'],
                            JSON_PRETTY_PRINT),
                        500,
                        'application/json'
                    );
                }

                $rawInput = file_get_contents('php://input');

                foreach (\IPS\Api\Key::roots(null) as $apiKey) {
                    $apiHash = hash_hmac($algo, $rawInput, $apiKey->id);
                    if (hash_equals($apiHash, $hash)) {
                        if (\IPS\Request::i()->isCgi() and isset(\IPS\Request::i()->key)) {
                            \IPS\Request::i()->key = $apiKey->id;
                        } else {
                            $_SERVER['PHP_AUTH_USER'] = $apiKey->id;
                        }
                        break;
                    }
                }
            }

            parent::run();
        } catch (\RuntimeException $e) {
            if (method_exists(get_parent_class(), __FUNCTION__)) {
                return \call_user_func_array('parent::' . __FUNCTION__, \func_get_args());
            } else {
                throw $e;
            }
        }
    }

}
