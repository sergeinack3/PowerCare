<?php

/**
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Admin;

use Ox\Core\CMbObject;
use Ox\Mediboard\Etablissement\CGroups;

/**
 * Association class between CSourceLDAP and CGroups
 */
class CSourceLDAPLink extends CMbObject
{
    /** @var integer Primary key */
    public $source_ldap_link_id;

    /** @var integer CSourceLDAP ID */
    public $source_ldap_id;

    /** @var integer CGroups ID */
    public $group_id;

    /** @var CSourceLDAP */
    public $_ref_source_ldap;

    /** @var CGroups */
    public $_ref_group;

    /**
     * @inheritdoc
     */
    function getSpec()
    {
        $spec        = parent::getSpec();
        $spec->table = 'source_ldap_link';
        $spec->key   = 'source_ldap_link_id';

        $spec->uniques['source_group'] = ['source_ldap_id', 'group_id'];

        return $spec;
    }

    /**
     * @inheritdoc
     */
    function getProps()
    {
        $props                   = parent::getProps();
        $props['source_ldap_id'] = 'ref class|CSourceLDAP notNull cascade back|source_ldap_links';
        $props['group_id']       = 'ref class|CGroups notNull back|source_ldap_links cascade';

        return $props;
    }

    /**
     * Loads the CSourceLDAP
     *
     * @param bool $cache Use cache
     *
     * @return CSourceLDAP|null
     */
    function loadRefSourceLDAP($cache = true)
    {
        return $this->_ref_source_ldap = $this->loadFwdRef('source_ldap_id', $cache);
    }

    /**
     * Loads the CGroups
     *
     * @param bool $cache Use cache
     *
     * @return CGroups|null
     */
    function loadRefGroup($cache = true)
    {
        return $this->_ref_group = $this->loadFwdRef('group_id', $cache);
    }
}
