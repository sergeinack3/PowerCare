{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function() {
    var form = getForm("editConfigSejour");
    form.elements["dPplanningOp[CSejour][max_cancel_time]"    ].addSpinner({min: 0, max: 24});
    form.elements["dPplanningOp[CSejour][hours_sejour_proche]"].addSpinner({min: 0, max: 96});
  });
</script>

<form name="editConfigSejour" method="post" onsubmit="return onSubmitFormAjax(this);">
  {{mb_configure module=$m}}

  {{assign var="class" value="CSejour"}}
  <table class="form">
    <tr>
      <th class="title" colspan="2">Général</th>
    </tr>

    {{mb_include module=system template=inc_config_bool var=use_session_praticien}}
    {{mb_include module=system template=inc_config_enum var=patient_id values=$patient_ids}}
    {{mb_include module=system template=inc_config_bool var=show_confirm_change_patient}}

    {{mb_include module=system template=inc_config_bool var=entree_modifiee}}

    {{mb_include module=system template=inc_config_enum var=heure_deb  values=$hours skip_locales=true}}
    {{mb_include module=system template=inc_config_enum var=heure_fin  values=$hours skip_locales=true}}

    {{mb_include module=system template=inc_config_enum var=min_intervalle  values=$intervals skip_locales=true}}

    {{mb_include module=system template=inc_config_bool var=blocage_occupation}}
    {{mb_include module=system template=inc_config_bool var=service_id_notNull}}
    {{mb_include module=system template=inc_config_bool var=consult_accomp}}
    {{mb_include module=system template=inc_config_bool var=delete_only_admin}}
    {{mb_include module=system template=inc_config_str  var=max_cancel_time     size=2 suffix=h}}
    {{mb_include module=system template=inc_config_str  var=hours_sejour_proche size=2 suffix=h}}

    {{mb_include module=system template=inc_config_bool var=use_recuse}}
    {{mb_include module=system template=inc_config_enum var=systeme_isolement values=standard|expert}}
    {{mb_include module=system template=inc_config_bool var=specified_output_mode}}
    <tr>
      <th class="title" colspan="2">
        Modes de traitement / entrée / sortie
      </th>
    </tr>
    <tr>
      <td colspan="2">
        {{mb_include module=system template=inc_config_bool class=CSejour var=show_only_charge_price_indicator}}
        {{mb_include module=system template=inc_config_bool class=CSejour var=use_custom_mode_entree}}
        {{mb_include module=system template=inc_config_bool class=CSejour var=use_custom_mode_sortie}}
      </td>
    </tr>

    <tr>
      <th class="title" colspan="2">{{tr}}CRegleSectorisation{{/tr}}</th>
    </tr>

    {{mb_include module=system template=inc_config_bool class=CRegleSectorisation var=use_sectorisation }}

    <tr>
      <td class="button" colspan="2">
        <button class="modify">{{tr}}Save{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>
