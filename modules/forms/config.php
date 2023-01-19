<?php
/**
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

$dPconfig["forms"] = array(
  "CExClassField" => array(
    "force_concept"            => 1,
    "doc_template_integration" => 0,
  ),
  "CExConcept"    => array(
    "force_list"   => 1,
    "native_field" => 0,
  ),
  "CExClass"      => array(
    "pixel_positionning"              => 0,
    "pixel_layout_delimiter"          => '1',
    "show_color_score_form"           => 1,
    "check_modification_before_close" => 1,
    "display_list_readonly"           => 0,
    'allowing_additional_columns'     => 0,
  ),
);
