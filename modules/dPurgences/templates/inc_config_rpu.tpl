{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function() {
    getForm("editConfig-RPU").elements["dPurgences[sibling_hours]"].addSpinner({min:0, max:24});
  });
</script>

<form name="editConfig-RPU" method="post" onsubmit="return onSubmitFormAjax(this);">
  {{mb_configure module=$m}}
  <table class="form">

    {{mb_include module=system template=inc_config_bool var=allow_change_patient}}
    {{mb_include module=system template=inc_config_bool var=only_prat_responsable}}
    {{mb_include module=system template=inc_config_bool var=gerer_reconvoc}}
    {{mb_include module=system template=inc_config_bool var=gerer_circonstance}}
    {{mb_include module=system template=inc_config_str  var=sibling_hours size="2" suffix="h"}}
    {{mb_include module=system template=inc_config_bool var=pec_change_prat}}
    {{mb_include module=system template=inc_config_bool var=pec_after_sortie}}
    {{mb_include module=system template=inc_config_bool var=create_sejour_hospit}}
    {{mb_include module=system template=inc_config_bool var=valid_cotation_sortie_reelle}}
    {{mb_include module=system template=inc_config_bool var=use_blocage_lit}}
    {{mb_include module=system template=inc_config_bool var=create_affectation}}
    {{mb_include module=system template=inc_config_num var=avis_maternite_refresh_frequency numeric=true}}

    <tr>
      <td class="button" colspan="2">
        <button class="modify">{{tr}}Save{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>
