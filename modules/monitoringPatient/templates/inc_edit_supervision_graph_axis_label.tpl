{{*
 * @package Mediboard\MonitoringPatient
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $label->_id}}
  <div class="small-warning">
    Si ce libellé est déjà utilisé, il est dangereux de le modifrer car cela impacte les graphiques déjà enregistrés.
  </div>
{{/if}}

<form name="edit-supervision-graph-axis-label" method="post" action="?m=dPpatients" onsubmit="return onSubmitFormAjax(this)">
  <input type="hidden" name="@class" value="CSupervisionGraphAxisValueLabel" />
  <input type="hidden" name="supervision_graph_axis_id" value="{{$label->supervision_graph_axis_id}}" />
  <input type="hidden" name="callback" value="SupervisionGraph.callbackAxisLabel" />
  {{mb_key object=$label}}
  
  <table class="main form">
    {{mb_include module=system template=inc_form_table_header object=$label}}

    <tr>
      <th>{{mb_label object=$label field=value}}</th>
      <td>{{mb_field object=$label field=value increment=true form="edit-supervision-graph-axis-label"}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$label field=title}}</th>
      <td>{{mb_field object=$label field=title}}</td>
    </tr>
    
    <tr>
      <th></th>
      <td>
        <button class="modify" type="submit">{{tr}}Save{{/tr}}</button>
        
        {{if $label->_id}}
          <button type="button" class="trash"
                  onclick="confirmDeletion(this.form,{ajax: true, typeName:'', objName:'{{$label->_view|smarty:nodefaults|JSAttribute}}'})">
            {{tr}}Delete{{/tr}}
          </button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>

