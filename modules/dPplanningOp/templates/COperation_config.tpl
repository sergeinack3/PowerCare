{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="editConfigOperation" method="post" onsubmit="return onSubmitFormAjax(this);">
  {{mb_configure module=$m}}

  {{assign var=class value=COperation}}

  <table class="form">
    <tr>
      <th class="title" colspan="2">Général</th>
    </tr>

    {{mb_include module=system template=inc_config_bool var=use_session_praticien}}
    {{mb_include module=system template=inc_config_bool var=use_ccam}}
    {{mb_include module=system template=inc_config_bool var=verif_cote}}
    {{mb_include module=system template=inc_config_bool var=delete_only_admin}}
    {{mb_include module=system template=inc_config_enum var=show_duree_uscpo values="0|1|2"}}
    {{mb_include module=system template=inc_config_enum var=default_week_stat_uscpo values="last|next"}}
    {{mb_include module=system template=inc_config_bool var=save_rank_annulee_validee}}
    {{mb_include module=system template=inc_config_bool var=cancel_only_for_resp_bloc}}
    {{mb_include module=system template=inc_config_enum var=duree_preop_adulte  values=$minutes skip_locales=true}}
    {{mb_include module=system template=inc_config_enum var=duree_preop_enfant  values=$minutes skip_locales=true}}
    {{mb_include module=system template=inc_config_enum var=duree_deb  values=$hours skip_locales=true}}
    {{mb_include module=system template=inc_config_enum var=duree_fin  values=$hours skip_locales=true}}
    {{mb_include module=system template=inc_config_enum var=hour_urgence_deb  values=$hours skip_locales=true}}
    {{mb_include module=system template=inc_config_enum var=hour_urgence_fin  values=$hours skip_locales=true}}
    {{mb_include module=system template=inc_config_str  var=nb_jours_urgence}}
    {{mb_include module=system template=inc_config_bool var=use_poste}}
    {{mb_include module=system template=inc_config_bool var=show_print_dhe_info}}

    {{mb_include module=system template=inc_config_enum var=min_intervalle values=$intervals skip_locales=true}}

    <tr>
      <th class="title" colspan="2">Affichage des champs</th>
    </tr>

    {{mb_include module=system template=inc_config_bool var=horaire_voulu}}
    {{mb_include module=system template=inc_config_bool var=show_duree_preop}}
    {{mb_include module=system template=inc_config_bool var=fiche_examen  }}
    {{mb_include module=system template=inc_config_bool var=fiche_materiel}}
    {{mb_include module=system template=inc_config_bool var=fiche_rques   }}
    {{mb_include module=system template=inc_config_bool var=show_secondary_function}}
    {{mb_include module=system template=inc_config_bool var=show_presence_op}}
    {{mb_include module=system template=inc_config_bool var=show_remarques}}
    {{mb_include module=system template=inc_config_bool var=show_montant_dp}}
    {{mb_include module=system template=inc_config_bool var=show_asa_position}}

    <tr>
      <td class="button" colspan="2">
        <button class="modify">{{tr}}Save{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>