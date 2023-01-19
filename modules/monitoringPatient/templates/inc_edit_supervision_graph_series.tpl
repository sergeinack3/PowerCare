{{*
 * @package Mediboard\MonitoringPatient
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="edit-supervision-graph-series" method="post" action="?m=dPpatients" onsubmit="return onSubmitFormAjax(this)">
  <input type="hidden" name="m" value="dPpatients" />
  <input type="hidden" name="del" value="0" />
  <input type="hidden" name="@class" value="CSupervisionGraphSeries" />
  <input type="hidden" name="supervision_graph_axis_id" value="{{$series->supervision_graph_axis_id}}" />
  <input type="hidden" name="callback" value="SupervisionGraph.callbackEditSeries" />
  <input type="hidden" name="datatype" value="NM" />
  {{mb_key object=$series}}
  
  <table class="main form">
    {{mb_include module=system template=inc_form_table_header object=$series}}
    
    <tr>
      <th>{{mb_label object=$series field=title}}</th>
      <td>{{mb_field object=$series field=title}}</td>
    </tr>
    
    <tr>
      <th>{{mb_label object=$series field=value_type_id}}</th>
      <td>{{mb_field object=$series field=value_type_id autocomplete="true,1,100,true,true" form="edit-supervision-graph-series" size=40}}</td>
    </tr>
    
    <tr>
      <th>{{mb_label object=$series field=value_unit_id}}</th>
      <td>{{mb_field object=$series field=value_unit_id autocomplete="true,1,100,true,true" form="edit-supervision-graph-series" size=40}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$series field=integer_values}}</th>
      <td>{{mb_field object=$series field=integer_values}}</td>
    </tr>
    
    <tr>
      <th>{{mb_label object=$series field=color}}</th>
      <td>{{mb_field object=$series field=color form="edit-supervision-graph-series"}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$series field=import_sampling_frequency}}</th>
      <td>{{mb_field object=$series field=import_sampling_frequency emptyLabel="Select"}}</td>
    </tr>
    
    <tr>
      <th class="category" colspan="2">
        {{tr}}CSupervisionGraphSeries-display_ratio_time{{/tr}}
      </th>
    </tr>
    
    <tr>
      <td colspan="2">
        <div class="small-info">
          Le paramétrage de l'échelle de temps empêchera de zoomer sur le graphique.
        </div>
      </td>
    </tr>
    
    <tr>
      <th>{{mb_label object=$series field=display_ratio_time}}</th>
      <td>
        {{mb_field object=$series field=display_ratio_value form="edit-supervision-graph-series" increment=true size=2}}
        unité(s)
        =
        {{mb_field object=$series field=display_ratio_time form="edit-supervision-graph-series" increment=true size=2}}
        minutes
      </td>
    </tr>
    
    <tr>
      <th></th>
      <td>
        <button class="modify" type="submit">{{tr}}Save{{/tr}}</button>
        
        {{if $series->_id}}
          <button type="button" class="trash"
                  onclick="confirmDeletion(this.form,{ajax: true, typeName:'', objName:'{{$series->_view|smarty:nodefaults|JSAttribute}}'})">
            {{tr}}Delete{{/tr}}
          </button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>
