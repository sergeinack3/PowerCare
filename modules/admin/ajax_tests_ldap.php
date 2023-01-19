<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbException;
use Ox\Core\CValue;
use Ox\Mediboard\Admin\CSourceLDAP;

CCanDo::checkAdmin();

$action         = CValue::get("action");
$source_ldap_id = CValue::get("source_ldap_id");
$ldaprdn        = CValue::get("ldaprdn");
$ldappass       = CValue::get("ldappass");
$filter         = CValue::get("filter");
$attributes     = CValue::get("attributes");

$source_ldap = new CSourceLDAP();
$source_ldap->load($source_ldap_id);

$results = [];

try {
    $ldapconn = $source_ldap->ldap_connect();
    CAppUI::stepAjax("CSourceLDAP_connect", UI_MSG_OK, $source_ldap->host);

    $source_ldap->ldap_bind($ldapconn, $ldaprdn, $ldappass, true);
    $user = $ldaprdn ?: "anonymous";
    $user = $source_ldap->bind_rdn_suffix ? ($ldaprdn . $source_ldap->bind_rdn_suffix) : $user;
    CAppUI::stepAjax("CSourceLDAP_authenticate", UI_MSG_OK, $source_ldap->host, $user);
} catch (CMbException $e) {
    $e->stepAjax(UI_MSG_ERROR);
}

if ($action == "search") {
    if (!$filter) {
        $filter = ($source_ldap->isAlternativeBinding()) ? "(cn=*)" : "(samaccountname=*)";
    }

    if ($attributes) {
        $attributes = preg_split("/\s*[,\n\|]\s*/", $attributes);
    }

    try {
        $results = $source_ldap->ldap_search($ldapconn, $filter, $attributes ?: []);
    } catch (CMbException $e) {
        $e->stepAjax(UI_MSG_ERROR);
    }

    CAppUI::stepAjax("CSourceLDAP_search-results", UI_MSG_OK, $filter);

    dump($results);
}
