<?php

/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

// Préférences par Module
use Ox\Mediboard\System\CPreferences;

CPreferences::$modules["dPpatients"] = [
    "allowed_modify_identity_status",
    "limit_prise_rdv",
    'edit_constant_when_not_creator',
    "allowed_to_edit_treatment",
    "allowed_to_edit_atcd",
    'allow_modify_strict_traits'
];
