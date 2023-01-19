{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=patients script=type_evenement_patient ajax=1}}

<script>
  refreshContentTypes = function () {
    var url = new Url("patients", "ajax_vw_types_evenement_patient");
    url.addParam("inner_content", '1');
    url.requestUpdate('vw_types_evenement_patient_container');
  };
</script>

{{if !$inner_content}}
<table class="main layout">
  <tr>
    <td id="vw_types_evenement_patient_container">
      {{/if}}

      <table class="tbl" style="width: 500px;">
        <tr>
          <th class="title" colspan="10">
            <button type="button" class="add notext" style="float: right;"
                    onclick="TypeEvenementPatient.edit(0, refreshContentTypes);">
            </button>
            {{tr}}CTypeEvenementPatient{{/tr}}
          </th>
        </tr>
        <tr>
          <th>{{mb_title class=CTypeEvenementPatient field=libelle}}</th>
          {{if $app->_ref_user->isAdmin()}}
            <th>{{mb_title class=CTypeEvenementPatient field=function_id}}</th>
          {{/if}}
          <th class="narrow">{{mb_title class=CTypeEvenementPatient field=notification}}</th>
          <th class="narrow">{{tr}}CTypeEvenementPatient-Mailing{{/tr}}</th>
          <th></th>
        </tr>
        {{foreach from=$types item=_type}}
          <tr>
            <td>{{$_type}}</td>
            {{if $app->_ref_user->isAdmin()}}
              <td>{{$_type->_ref_function}}</td>
            {{/if}}
            <td class="narrow" style="text-align: center;">
              {{if $_type->notification}}
                <i class="fa fa-lg fa-check" style="color: forestgreen;" title="Activées"></i>
              {{else}}
                <i class="fa fa-lg fa-times" style="color: firebrick;" title="Désactivées"></i>
              {{/if}}
            </td>
            <td class="narrow" style="text-align: center;">
              {{if $_type->mailing_model_id}}
                <i class="fa fa-lg fa-check" style="color: forestgreen;" title="Activées"></i>
              {{else}}
                <i class="fa fa-lg fa-times" style="color: firebrick;" title="Désactivées"></i>
              {{/if}}
            </td>
            <td class="narrow">
              {{if $app->_ref_user->isAdmin() || $_type->function_id}}
                <button type="button" class="edit notext" onclick="TypeEvenementPatient.edit({{$_type->_id}}, refreshContentTypes);">
                  {{tr}}Edit{{/tr}}
                </button>
              {{/if}}
            </td>
          </tr>
          {{foreachelse}}
          <tr>
            <td colspan="10" class="empty">{{tr}}CTypeEvenementPatient.none{{/tr}}</td>
          </tr>
        {{/foreach}}
      </table>

      {{if !$inner_content}}
    </td>
  </tr>
</table>
{{/if}}