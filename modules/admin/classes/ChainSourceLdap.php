<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Admin;

use Exception;
use Ox\Core\CMbException;

/**
 * Chain adapter for CSourceLDAP.
 */
class ChainSourceLdap
{
    /** @var CSourceLDAP[] */
    private $sources = [];

    /** @var CSourceLDAP[] */
    private $bound_sources = [];

    /**
     * @param CSourceLDAP ...$sources
     */
    public function __construct(CSourceLDAP ...$sources)
    {
        $this->sources = $sources;
    }

    /**
     * Tell if all sources are unreachable.
     *
     * @return bool
     */
    public function areUnreachable(): bool
    {
        foreach ($this->sources as $_source) {
            if ($_source->_ldapconn) {
                return false;
            }
        }

        return true;
    }

    /**
     * Bind the sources
     *
     * @param bool        $show_invalid_credentials
     * @param string|null $username
     * @param string|null $password
     *
     * @return bool
     * @throws CMbLDAPPasswordExpiredException
     */
    public function bind(bool $show_invalid_credentials, string $username = null, string $password = null): bool
    {
        foreach ($this->sources as $_source) {
            try {
                $_username = ($username) ?: $_source->user;
                $_password = ($password) ?: $_source->password;

                if ($_source->ldap_bind($_source->_ldapconn, $_username, $_password, $show_invalid_credentials)) {
                    $this->bound_sources[$_source->_id] = $_source;
                }
            } catch (CMbLDAPPasswordExpiredException $e) {
                throw $e;
            } catch (CMbException $e) {
                continue;
            }
        }

        return (count($this->bound_sources) > 0);
    }

    /**
     * Search among bound sources.
     *
     * @param string|null $username
     * @param string|null $firstname
     * @param string|null $lastname
     *
     * @return array
     * @throws Exception
     */
    public function search(?string $username, ?string $firstname, ?string $lastname): array
    {
        $results_by_source          = array_fill_keys(array_keys($this->bound_sources), []);
        $results_by_source['count'] = 0;

        foreach ($this->bound_sources as $_source) {
            $choose_filter = '';

            if ($username) {
                $choose_filter = $_source->isAlternativeBinding() ? "(cn={$username}*)" : "(samaccountname=$username*)";
            }

            if ($firstname) {
                $choose_filter .= "(givenname=$firstname*)";
            }

            if ($lastname) {
                $choose_filter .= "(sn=$lastname*)";
            }

            $filter = "(|$choose_filter)";
            $filter = utf8_encode($filter);

            try {
                $results_by_source[$_source->_id] = $_source->ldap_search($_source->_ldapconn, $filter);
                $results_by_source['count']       += $results_by_source[$_source->_id]['count'];
            } catch (CMbException $e) {
                continue;
            }
        }

        return $results_by_source;
    }

    /**
     * Filter among bound sources.
     *
     * @param string|null $filter
     * @param string|null $alternative_filter
     *
     * @return array
     * @throws Exception
     */
    public function filter(?string $filter, ?string $alternative_filter): array
    {
        $results_by_source          = array_fill_keys(array_keys($this->bound_sources), []);
        $results_by_source['count'] = 0;

        foreach ($this->bound_sources as $_source) {
            try {
                $_filter = ($_source->isAlternativeBinding()) ? $alternative_filter : $filter;

                $results_by_source[$_source->_id] = $_source->ldap_search($_source->_ldapconn, $_filter);
                $results_by_source['count']       += $results_by_source[$_source->_id]['count'];
            } catch (CMbException $e) {
                continue;
            }
        }

        return $results_by_source;
    }

    /**
     * Search and map a user among bound sources.
     *
     * @param CUser $user
     * @param       $person
     * @param       $filter
     * @param       $force_create
     * @param       $check_password_expiration
     *
     * @return CUser
     */
    public function searchAndMap(
        CUser $user,
              $person = null,
              $filter = null,
              $force_create = false,
              $check_password_expiration = true
    ): CUser {
        foreach ($this->bound_sources as $_source) {
            try {
                $new_user = CLDAP::searchAndMap(
                    $user,
                    $_source,
                    $_source->_ldapconn,
                    $person,
                    $filter,
                    $force_create,
                    $check_password_expiration
                );

                if ($new_user->_bound) {
                    return $new_user;
                }
            } catch (CMbException $e) {
                continue;
            }
        }

        return $user;
    }

    public function startTls(): void
    {
        foreach ($this->bound_sources as $_source) {
            $_source->start_tls();
        }
    }

    /**
     * @param string $name
     *
     * @return string
     * @throws CMbException
     */
    public function getDn(string $name): string
    {
        foreach ($this->bound_sources as $_source) {
            try {
                return $_source->get_dn($name);
            } catch (CMbException $e) {
                continue;
            }
        }

        throw new CMbException("CSourceLDAP_too-many-results");
    }

    /**
     * @param string $dn
     * @param array  $entry
     *
     * @return bool
     */
    public function ldapModReplace(string $dn, array $entry): bool
    {
        foreach ($this->bound_sources as $_source) {
            try {
                $_source->ldap_mod_replace($entry, $_source->_ldapconn, $dn);
            } catch (CMbException $e) {
                continue;
            }
        }

        return true;
    }

    /**
     * Return a bound CSourceLDAP by id.
     *
     * @param int $id
     *
     * @return CSourceLDAP
     * @throws Exception
     */
    public function getBoundSourceById(int $id): CSourceLDAP
    {
        if (!isset($this->bound_sources[$id])) {
            throw new Exception();
        }

        return $this->bound_sources[$id];
    }
}
