<?php
/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

$dPconfig["dPurgences"] = array(
  "date_tolerance"                       => "2",
  "old_rpu"                              => "0",
  "rpu_warning_time"                     => "00:20:00",
  "rpu_alert_time"                       => "01:00:00",
  "default_view"                         => "tous",
  "allow_change_patient"                 => "1",
  "age_patient_rpu_view"                 => "0",
  "responsable_rpu_view"                 => "1",
  "sortie_prevue"                        => "sameday",
  "only_prat_responsable"                => "0",
  "gerer_reconvoc"                       => "1",
  "sibling_hours"                        => "0",
  "pec_change_prat"                      => "1",
  "pec_after_sortie"                     => "0",
  "create_sejour_hospit"                 => "0",
  "hide_reconvoc_sans_sortie"            => "0",
  "show_statut"                          => "0",
  "attente_first_part"                   => "00:30:00",
  "attente_second_part"                  => "02:00:00",
  "attente_third_part"                   => "04:00:00",
  "gerer_circonstance"                   => "0",
  "valid_cotation_sortie_reelle"         => "1",
  "display_regule_par"                   => "0",
  "view_rpu_uhcd"                        => "0",
  "use_blocage_lit"                      => "0",
  "create_affectation"                   => "1",
  "main_courante_refresh_frequency"      => "90",
  "uhcd_refresh_frequency"               => "180",
  "imagerie_refresh_frequency"           => "180",
  "identito_vigilance_refresh_frequency" => "300",
  "avis_maternite_refresh_frequency"     => "180",
  "use_vue_topologique"                  => "1",
  "vue_topo_refresh_frequency"           => "90",
  "CExtractPassages"                     => array(
    "purge_probability"      => "100",
    "purge_empty_threshold"  => "28",
    "purge_delete_threshold" => "168"
  ),
);
