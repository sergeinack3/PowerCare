{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  editDirective = function (directive_anticipee_id, patient_id) {
    var url = new Url("patients", "ajax_edit_directive");
    url.addParam("directive_anticipee_id", directive_anticipee_id);
    url.addParam("patient_id", patient_id);
    url.requestModal(null, null, {
      onClose: function () {
        Control.Modal.refresh();

        AnticipatedDirectives.number_directives = $$('.a-directive').length;

        // Can't wait for the end of refresh (bypass async)
        if (AnticipatedDirectives.adding_directive) {
          AnticipatedDirectives.number_directives++;
          // Reset the prop for an eventual next use
          AnticipatedDirectives.adding_directive = null;
        }
        if (AnticipatedDirectives.removing_directive) {
          AnticipatedDirectives.number_directives--;
          // Reset the prop for an eventual next use
          AnticipatedDirectives.removing_directive = null;
        }
      }
    });
  };
</script>

{{assign var=permission_directive value=true}}
{{if "soins synthese can_edit_directives"|gconf == "praticien" && !$app->_ref_user->isPraticien() && !$app->_ref_user->isAdmin()}}
  {{assign var=permission_directive value=false}}
{{/if}}

<button class="new" type="button" onclick="editDirective(0, '{{$patient->_id}}');" {{if !$permission_directive}}disabled{{/if}}>
  {{tr}}CDirectiveAnticipee-action-New directive|pl{{/tr}}
</button>

<table class="main tbl">
  <tr>
    <th class="title" colspan="8">
      {{tr}}CDirectiveAnticipee-List of directive{{/tr}}
    </th>
  </tr>
  <tr>
    <th class="narrow"></th>
    <th>{{mb_label class=CDirectiveAnticipee field=date_recueil}}</th>
    <th>{{mb_label class=CDirectiveAnticipee field=date_validite}}</th>
    <th class="text">{{mb_label class=CDirectiveAnticipee field=description}}</th>
    <th class="narrow">{{mb_label class=CDirectiveAnticipee field=detenteur_id}}</th>
  </tr>
  {{foreach from=$directives item=_directive}}
    <tr class="a-directive">
      <td class="button">
        <button type="button" class="edit notext" onclick="editDirective('{{$_directive->_id}}', '{{$patient->_id}}');"
                title="{{tr}}common-action-Edit{{/tr}}" {{if !$permission_directive}}disabled{{/if}}>
          {{tr}}common-action-Edit{{/tr}}
        </button>
      </td>
      <td>{{mb_value object=$_directive field=date_recueil}}</td>
      <td>{{mb_value object=$_directive field=date_validite}}</td>
      <td>{{mb_value object=$_directive field=description}}</td>
      <td>
          <span onmouseover="ObjectTooltip.createEx(this, '{{$_directive->_ref_detenteur->_guid}}')">
            {{if $_directive->_ref_detenteur|instanceof:'Ox\Mediboard\Patients\CPatient'}}
              {{$_directive->_ref_detenteur->_view}}
            {{else}}
              {{$_directive->_ref_detenteur->_longview}}
            {{/if}}

          </span>
      </td>
    </tr>
    {{foreachelse}}
    <tr>
      <td colspan="10" class="empty">{{tr}}CDirectiveAnticipee-No directive{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>
