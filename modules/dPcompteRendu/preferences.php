<?php
/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Mediboard\System\CPreferences;

/**
 * Préférences utilisateur
 */
// Préférences par Module
CPreferences::$modules["dPcompteRendu"] = array(
  "saveOnPrint",
  "choicepratcab",
  "listDefault",
  "listBrPrefix",
  "listInlineSeparator",
  "aideTimestamp",
  "aideOwner",
  "aideFastMode",
  "aideAutoComplete",
  "aideShowOver",
  "pdf_and_thumbs",
  "mode_play",
  "multiple_docs",
  "auto_capitalize",
  "auto_replacehelper",
  'hprim_med_header',
  "show_old_print",
  "send_document_subject",
  "send_document_body",
  "multiple_doc_correspondants",
  "show_creation_date",
  "secure_signature",
  "check_to_empty_field",
  "time_autosave",
  "show_favorites"
);
