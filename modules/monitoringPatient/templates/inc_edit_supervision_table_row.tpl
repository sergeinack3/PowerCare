{{*
 * @package Mediboard\MonitoringPatient
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}


<form name="edit-supervision-table-row" method="post" onsubmit="return onSubmitFormAjax(this);">
  {{mb_class object=$row}}
  {{mb_key   object=$row}}
  <input type="hidden" name="del" value="0" />
  <input type="hidden" name="supervision_table_id" value="{{$row->supervision_table_id}}" />
  <input type="hidden" name="callback" value="SupervisionGraph.callbackEditTableRow" />

  <table class="main form me-margin-top-0 me-no-border-radius-top me-no-align">
    {{mb_include module=system template=inc_form_table_header object=$row colspan=4}}

    <tr>
      <th>{{mb_label object=$row field=title}}</th>
      <td>{{mb_field object=$row field=title}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$row field=active}}</th>
      <td>{{mb_field object=$row field=active}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$row field=color}}</th>
      <td>{{mb_field object=$row field=color form="edit-supervision-table-row"}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$row field=value_type_id}}</th>
      <td>{{mb_field object=$row field=value_type_id autocomplete="true,1,100,true,true" form="edit-supervision-table-row" size=40}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$row field=value_unit_id}}</th>
      <td>{{mb_field object=$row field=value_unit_id autocomplete="true,1,100,true,true" form="edit-supervision-table-row" size=40}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$row field=import_sampling_frequency}}</th>
      <td>{{mb_field object=$row field=import_sampling_frequency emptyLabel="Select"}}</td>
    </tr>
    <td class="button" colspan="2">
      <button class="modify" type="submit">{{tr}}Save{{/tr}}</button>

      {{if $row->_id}}
        <button type="button" class="trash"
                onclick="confirmDeletion(this.form, {ajax: true, typeName: '', objName: '{{$row->_view|smarty:nodefaults|JSAttribute}}'});">
          {{tr}}Delete{{/tr}}
        </button>
      {{/if}}
    </td>
    </tr>
  </table>
</form>