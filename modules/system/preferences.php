<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Mediboard\System\CPreferences;

// Préférences par Module
CPreferences::$modules["common"] = array (
  "LOCALE",
  "FALLBACK_LOCALE",
  "UISTYLE",
  "mediboard_ext",
  "MenuPosition",
  "DEFMODULE",
  "touchscreen",
  "accessibility_dyslexic",
  "tooltipAppearenceTimeout",
  "useEditAutocompleteUsers",
  "autocompleteDelay",
  "showCounterTip",
  "textareaToolbarPosition",
  "sessionLifetime",
  "planning_resize",
  "planning_dragndrop",
  "planning_hour_division",
  "notes_anonymous",
  "navigationHistoryLength",
  "displayUTCDate",
);
  
CPreferences::$modules["system"] = array (
  "INFOSYSTEM",
  "show_performance"
);
