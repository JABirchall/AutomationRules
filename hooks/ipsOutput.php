//<?php

class rules_hook_ipsOutput extends _HOOK_CLASS_
{

    /**
     * Send output
     *
     * @param string $output Content to output
     * @param int $httpStatusCode HTTP Status Code
     * @param string $contentType HTTP Content-type
     * @param array $httpHeaders Additional HTTP Headers
     * @param bool $cacheThisPage Can/should this page be cached?
     * @param bool $pageIsCached Is the page from a cache? If TRUE, no language parsing will be done
     * @return    void
     */
    public function sendOutput(
        $output = '',
        $httpStatusCode = 200,
        $contentType = 'text/html',
        $httpHeaders = [],
        $cacheThisPage = true,
        $pageIsCached = false,
        $parseFileObjects = true
    ) {
        try {
            \IPS\rules\Event::load('rules', 'System', 'browser_output')->trigger(
                $output,
                $httpStatusCode,
                $contentType,
                $httpHeaders,
                $cacheThisPage,
                $pageIsCached
            );

            /* Shut down rules early for actual page requests (so redirects can be performed, etc) */
            \IPS\rules\Application::shutDown();

            return call_user_func_array('parent::sendOutput', func_get_args());
        } catch (\RuntimeException $e) {
            if (method_exists(get_parent_class(), __FUNCTION__)) {
                return \call_user_func_array('parent::' . __FUNCTION__, \func_get_args());
            } else {
                throw $e;
            }
        }
    }

    /**
     * Redirect
     *
     * @param \IPS\Http\Url $url URL to redirect to
     * @param string $message Optional message to display
     * @param int $httpStatusCode HTTP Status Code
     * @param bool $forceScreen If TRUE, an intermediate screen will be shown
     * @return    void
     */
    public function redirect($url, $message = '', $httpStatusCode = 301, $forceScreen = false)
    {
        try {
            if (static::$instance->inlineMessage) {
                $_SESSION['inlineMessage'] = static::$instance->inlineMessage;
            }

            return parent::redirect($url, $message, $httpStatusCode, $forceScreen);
        } catch (\RuntimeException $e) {
            if (method_exists(get_parent_class(), __FUNCTION__)) {
                return \call_user_func_array('parent::' . __FUNCTION__, \func_get_args());
            } else {
                throw $e;
            }
        }
    }

}