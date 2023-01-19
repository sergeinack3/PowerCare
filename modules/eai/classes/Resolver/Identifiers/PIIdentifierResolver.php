<?php

/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Eai\Resolver\Identifiers;

use Ox\Interop\Eai\CDomain;
use Ox\Mediboard\Etablissement\CGroups;

/**
 * Algorithme for resolve if identifier is a 'Patient internal identifier' (PI)
 */
class PIIdentifierResolver
{
    /** @var string */
    private const SEARCH_MODE_NAMESPACE = 'mode_namespace_id';
    /** @var string */
    private const SEARCH_MODE_OID = 'mode_oid';
    /** @var string */
    private const SEARCH_MODE_DOWNGRADE = 'mode_downgrade';

    private bool $control_type_identifier = true;

    private string $search_mode = self::SEARCH_MODE_NAMESPACE;

    private string $type_code_expected = 'PI';

    private ?CGroups $group = null;

    private ?CDomain $domain = null;

    /**
     * Resolve if identifier is a 'Patient internal identifier' (PI)
     *
     * @param string|null $identifier
     * @param string|null $system_identifier
     * @param string|null $type_code
     *
     * @return string|null
     */
    public function resolve(?string $identifier, ?string $system_identifier, ?string $type_code): ?string
    {
        if (!$system_identifier && !$type_code) {
            return null;
        }

        // mode downgrade
        if ($this->search_mode === self::SEARCH_MODE_DOWNGRADE) {
            return $type_code === $this->type_code_expected ? $identifier : null;
        }

        // retrieve domain
        if (!$domain = $this->domain) {
            if (!$this->group) {
                $this->group = CGroups::loadCurrent();
            }

            $this->domain = $domain = CDomain::getMasterDomainPatient($this->group->_id);
        }

        // control oid || namespace id
        if ($this->search_mode === self::SEARCH_MODE_NAMESPACE) {
            if ($domain->namespace_id === null || !$this->compareSystem($system_identifier, $domain->namespace_id)) {
                return null;
            }
        } elseif ($this->search_mode === self::SEARCH_MODE_OID) {
            if ($domain->OID === null || !$this->compareSystem($system_identifier, $domain->OID)) {
                return null;
            }
        }

        // control type
        if ($this->control_type_identifier) {
            return $type_code === $this->type_code_expected ? $identifier : null;
        }

        // no control of type
        return $identifier;
    }

    private function compareSystem(?string $system, string $system_expected) {
        return preg_match("/^(?:urn:(?:uuid|oid):)?$system_expected$/", $system);
    }

    /**
     * Disable control for type identifier
     *
     * @return PIIdentifierResolver
     */
    public function disableControlTypeIdentifier(): PIIdentifierResolver
    {
        $this->control_type_identifier = false;

        return $this;
    }

    /**
     * @param string $type_identifier_code_expected
     *
     * @return PIIdentifierResolver
     */
    public function setCustomTypeCodeExpected(string $custom_type_code): PIIdentifierResolver
    {
        $this->type_code_expected = $custom_type_code;

        return $this;
    }

    /**
     * Check oid identifier in domain
     *
     * @return self
     */
    public function setModeOID(): self
    {
        $this->search_mode = self::SEARCH_MODE_OID;

        return $this;
    }

    /**
     * Check namespace id identifier in domain
     *
     * @return self
     */
    public function setModeNamespaceId(): self
    {
        $this->search_mode = self::SEARCH_MODE_NAMESPACE;

        return $this;
    }

    /**
     * Mode without check namespace id or oid (downgraded mode)
     *
     * @return self
     */
    public function setModeDowngrade(): self
    {
        $this->search_mode = self::SEARCH_MODE_DOWNGRADE;

        return $this;
    }

    /**
     * @param CGroups|null $group
     *
     * @return PIIdentifierResolver
     */
    public function setGroup(?CGroups $group): PIIdentifierResolver
    {
        $this->group = $group;

        return $this;
    }

    /**
     * @param CDomain|null $domain
     *
     * @return PIIdentifierResolver
     */
    public function setDomain(?CDomain $domain): PIIdentifierResolver
    {
        $this->domain = $domain;

        return $this;
    }
}
