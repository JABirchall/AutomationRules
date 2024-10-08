<?php
/**
 * @brief        Editor Extension: Generic
 * @author        <a href='http://www.invisionpower.com'>Invision Power Services, Inc.</a>
 * @copyright    (c) 2001 - SVN_YYYY Invision Power Services, Inc.
 * @license        http://www.invisionpower.com/legal/standards/
 * @package        IPS Social Suite
 * @subpackage    Rules
 * @since        19 Mar 2015
 * @version        SVN_VERSION_NUMBER
 */

namespace IPS\rules\extensions\core\EditorLocations;

/* To prevent PHP errors (extending class does not exist) revealing path */
if (!defined('\IPS\SUITE_UNIQUE_KEY')) {
    header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}

/**
 * Editor Extension: Generic
 */
class _Generic
{
    /**
     * Can we use HTML in this editor?
     *
     * @param \IPS\Member $member The member
     * @return    bool|null    NULL will cause the default value (based on the member's permissions) to be used, and is recommended in most cases. A boolean value will override that.
     */
    public function canUseHtml($member)
    {
        return null;
    }

    /**
     * Can we use attachments in this editor?
     *
     * @param \IPS\Member $member The member
     * @param \IPS\Helpers\Form\Editor $field The editor field
     * @return    bool|null    NULL will cause the default value (based on the member's permissions) to be used, and is recommended in most cases. A boolean value will override that.
     */
    public function canAttach($member, $field)
    {
        return false;
    }

    /**
     * Permission check for attachments
     *
     * @param \IPS\Member $member The member
     * @param int|null $id1 Primary ID
     * @param int|null $id2 Secondary ID
     * @param string|null $id3 Arbitrary data
     * @param array $attachment The attachment data
     * @return    bool
     */
    public function attachmentPermissionCheck($member, $id1, $id2, $id3, $attachment)
    {
        return true;
    }

    /**
     * Attachment lookup
     *
     * @param int|null $id1 Primary ID
     * @param int|null $id2 Secondary ID
     * @param string|null $id3 Arbitrary data
     * @return    \IPS\Http\Url|\IPS\Content|\IPS\Node\Model
     * @throws    \LogicException
     */
    public function attachmentLookup($id1, $id2, $id3)
    {
    }


}