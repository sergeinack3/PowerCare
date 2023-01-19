<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

$dPconfig["dPpatients"] = array(          //config/setup/check
  "CPatient"             => array(
    "function_distinct"  => "0",
    "tag_ipp"            => "",
    "tag_ipp_group_idex" => "",
    "tag_ipp_trash"      => "trash_",
    "tag_conflict_ipp"   => "conflict_",
  ),
  "CConstantesMedicales" => array(
    "unite_ta"        => "cmHg",        //Déjà migré, utiliser addDefaultConfig
    "unite_glycemie"  => "g/l",       //Déjà migré, utiliser addDefaultConfig
    "unite_cetonemie" => "g/l",       //Déjà migré, utiliser addDefaultConfig
  ),

  "CAntecedent" => array(
    "types"           => "med|alle|trans|obst|chir|fam|anesth|gyn",
    "mandatory_types" => "",
    "appareils"       => "cardiovasculaire|digestif|endocrinien|neuro_psychiatrique|pulmonaire|uro_nephrologique",
  ),

  "CTraitement" => array(
    "enabled" => "1",
  ),

  "CDossierMedical" => array(),

  "imports" => array(
    "pat_csv_path" => "",
    "pat_start"    => 0,
    "pat_count"    => 20,

    "sejour_csv_path" => "",
  ),

  "INSEE" => array(
    "france"    => "1",
    "suisse"    => "0",
    "allemagne" => "0",
    "espagne"   => "0",
    "portugal"  => "0",
    "gb"        => "0",
    "belgique"  => "0",
  ),

  "import_tag"    => "",
  "file_date_min" => "1970-01-01",
  "file_date_max" => "2020-12-31",
);
