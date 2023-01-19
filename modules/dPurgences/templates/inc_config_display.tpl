{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="editConfig-Display" method="post" onsubmit="return onSubmitFormAjax(this);">
  {{mb_configure module=$m}}
  <table class="form">
    {{mb_include module=system template=inc_config_str var=rpu_warning_time}}
    {{mb_include module=system template=inc_config_str var=rpu_alert_time}}
    {{mb_include module=system template=inc_config_str var=date_tolerance}}
    {{mb_include module=system template=inc_config_enum var=default_view values="tous|presents"}}
    {{mb_include module=system template=inc_config_bool var=age_patient_rpu_view}}
    {{mb_include module=system template=inc_config_bool var=responsable_rpu_view}}
    {{mb_include module=system template=inc_config_bool var=hide_reconvoc_sans_sortie}}
    {{mb_include module=system template=inc_config_str var=attente_first_part}}
    {{mb_include module=system template=inc_config_str var=attente_second_part}}
    {{mb_include module=system template=inc_config_str var=attente_third_part}}
    {{mb_include module=system template=inc_config_bool var=display_regule_par}}
    {{mb_include module=system template=inc_config_bool var=view_rpu_uhcd}}
    {{mb_include module=system template=inc_config_enum var=main_courante_refresh_frequency      values="90|180|300|600"}}
    {{mb_include module=system template=inc_config_enum var=uhcd_refresh_frequency               values="90|180|300|600"}}
    {{mb_include module=system template=inc_config_enum var=imagerie_refresh_frequency           values="90|180|300|600"}}
    {{mb_include module=system template=inc_config_enum var=identito_vigilance_refresh_frequency values="90|180|300|600"}}
    {{mb_include module=system template=inc_config_enum var=vue_topo_refresh_frequency           values="90|180|300|600"}}

    <tr>
      <td class="button" colspan="2">
        <button class="modify">{{tr}}Save{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>
