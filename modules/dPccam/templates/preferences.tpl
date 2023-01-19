{{*
 * @package Mediboard\Ccam
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_include template=inc_pref spec=bool var=new_search_ccam}}
{{mb_include template=inc_pref spec=bool var=multiple_select_ccam}}
{{mb_include template=inc_pref spec=bool var=user_executant}}
{{mb_include template=inc_pref spec=bool var=actes_comp_supp_favoris}}
{{mb_include template=inc_pref spec=bool var=precode_modificateur_7}}
{{mb_include template=inc_pref spec=bool var=precode_modificateur_J}}
{{mb_include template=inc_pref spec=bool var=spread_modifiers}}
{{mb_include template=inc_pref spec=enum var=default_qualif_depense values='|d|e|f|g|n|a|b|l'}}
{{mb_include template=inc_pref spec=enum var=preselected_filters_ngap_sejours values='CMediusers|CFunctions'}}
{{mb_include template=inc_pref spec=bool var=use_ccam_acts}}
{{mb_include template=inc_pref spec=bool var=enabled_majoration_F values='0|1'}}
