{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=patients script=patient ajax=$ajax}}

{{assign var=function_distinct value=$conf.dPpatients.CPatient.function_distinct}}

<script>
  Main.add(function () {
    var form = getForm('addInfoGroup');
    new Url('system', 'ajax_seek_autocomplete')
      .addParam('object_class', 'CPatient')
      .addParam('field', 'patient_id')
      .addParam('input_field', '_patient_view')
      {{if $function_distinct && !$app->_ref_user->isAdmin()}}
        {{if $function_distinct == 1}}
        .addParam('where[function_id]', '{{$app->_ref_user->function_id}}')
        {{else}}
        .addParam('where[group_id]', '{{$g}}')
        {{/if}}
      {{/if}}
      .autoComplete(form.elements['_patient_view'], null, {
        minchars:           3,
        method:             'get',
        select:             'view',
        dropdown:           false,
        width:              '300px',
        afterUpdateElement: function (field, selected) {
          $V(field.form.elements['patient_id'], selected.get('guid').split('-')[1]);
        }
      });
  });

  emptyPatient = function (form) {
    $V(form.elements['patient_id'], '');
    $V(form.elements['_patient_view'], '');
  }
</script>

<!-- Modale de creation / modification d'une information groupe -->
<form name="addInfoGroup" action="?m={{$m}}" method="post" onsubmit="return onSubmitFormAjax(this, Control.Modal.close);">
  {{mb_class object=$info_group}}
  {{mb_key   object=$info_group}}

  {{mb_field object=$info_group field=user_id hidden=true}}
  {{mb_field object=$info_group field=group_id hidden=true}}
  {{mb_field object=$info_group field=date hidden=true}}
  {{mb_field object=$info_group field=service_id hidden=true}}

  <table class="form">
    <tr>
      <th colspan="2" class="title {{if $info_group->_id}} modify{{/if}}">
        {{if $info_group->_id}}
          {{tr}}CInfoGroup-title-modify{{/tr}}
        {{else}}
          {{tr}}CInfoGroup-title-create{{/tr}}
        {{/if}}
      </th>
    </tr>
    <tr>
      <th>
        {{mb_label object=$info_group field=actif}}
      </th>
      <td>
        {{mb_field object=$info_group field=actif form="addInfoGroup"}}
      </td>
    </tr>
    <tr>
      <th>
        {{mb_label object=$info_group field=patient_id}}
      </th>
      <td>
        {{mb_field object=$info_group field=patient_id hidden=true}}
        <input type="text" name="_patient_view"
               value="{{if $info_group->patient_id && $info_group->_ref_patient}}{{$info_group->_ref_patient}}{{/if}}">
        <button type="button" class="cancel notext" onclick="emptyPatient(this.form);">{{tr}}Empty{{/tr}}</button>
      </td>
    </tr>
    {{if 'dPhospi CInfoGroup split_by_users'|gconf || $force_type}}
      <tr>
        <th>
          {{mb_label object=$info_group field=type_id}}
        </th>
        <td>
          {{mb_field object=$info_group field=type_id form='addInfoGroup' autocomplete="true,1,100,true,true"}}
        </td>
      </tr>
    {{/if}}
    <tr>
      <td colspan="2">
        {{mb_label object=$info_group field="description"}}
        {{mb_field object=$info_group field="description" form="addInfoGroup" style="height: 350px;" aidesaisie="validateOnBlur: 0, strict: 0" }}
      </td>
    </tr>
    <tr>
      <td colspan="2" class="button">
        <button class="submit">{{tr}}Save{{/tr}}</button>
        {{if $info_group->_id}}
          <button type="button" class="trash"
                  onclick="confirmDeletion(this.form, {
                    ajax: true,
                    objName:'cet élément'}, Control.Modal.close);">{{tr}}Delete{{/tr}}</button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>
