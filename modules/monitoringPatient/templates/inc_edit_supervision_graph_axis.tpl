{{*
 * @package Mediboard\MonitoringPatient
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    {{if $axis->_id}}
    SupervisionGraph.listSeries({{$axis->_id}});
    SupervisionGraph.listAxisLabels({{$axis->_id}});
    {{/if}}

    var row = $$("tr[data-axis_id={{$axis->_id}}]")[0];
    if (row) {
      row.addUniqueClassName("selected");
    }

    Control.Tabs.create("axis-tabs");
  });
</script>

<form name="edit-supervision-graph-axis" method="post" onsubmit="return onSubmitFormAjax(this);">
  {{mb_class object=$axis}}
  {{mb_key   object=$axis}}
  <input type="hidden" name="del" value="0" />
  <input type="hidden" name="supervision_graph_id" value="{{$axis->supervision_graph_id}}" />
  <input type="hidden" name="callback" value="SupervisionGraph.callbackEditAxis" />

  <table class="main form me-margin-top-0 me-margin-bottom-0 me-no-border-radius-top me-no-border-radius-bottom me-no-box-shadow">
    {{mb_include module=system template=inc_form_table_header object=$axis colspan=4}}
    
    <tr>
      <th>{{mb_label object=$axis field=title}}</th>
      <td>{{mb_field object=$axis field=title}}</td>
      
      <th>{{mb_label object=$axis field=display}}</th>
      <td>{{mb_field object=$axis field=display emptyLabel="Seulement les points"}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$axis field=limit_low}}</th>
      <td>{{mb_field object=$axis field=limit_low increment=true form="edit-supervision-graph-axis"}}</td>
      
      <th>{{mb_label object=$axis field=limit_high}}</th>
      <td>{{mb_field object=$axis field=limit_high increment=true form="edit-supervision-graph-axis"}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$axis field=show_points typeEnum=checkbox}}</th>
      <td>{{mb_field object=$axis field=show_points typeEnum=checkbox}}</td>
      
      <th>{{mb_label object=$axis field=symbol}}</th>
      <td>{{mb_field object=$axis field=symbol}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$axis field=in_doc_template typeEnum=checkbox}}</th>
      <td colspan="3">{{mb_field object=$axis field=in_doc_template typeEnum=checkbox}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$axis field=actif typeEnum=checkbox}}</th>
      <td colspan="3">{{mb_field object=$axis field=actif typeEnum=checkbox}}</td>
    </tr>
    <tr>
      <th></th>
      <td colspan="3">
        <button class="modify" type="submit">{{tr}}Save{{/tr}}</button>
        
        {{if $axis->_id}}
          <button type="button" class="trash"
                  onclick="confirmDeletion(this.form, {ajax: true, typeName: '', objName: '{{$axis->_view|smarty:nodefaults|JSAttribute}}'});">
            {{tr}}Delete{{/tr}}
          </button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>


<ul class="control_tabs me-margin-top-0" id="axis-tabs">
  <li><a href="#supervision-graph-series-list">Séries</a></li>
  <li><a href="#supervision-graph-axis-labels-list">Libellés d'axes</a></li>
</ul>
<div id="supervision-graph-series-list" style="display: none;"></div>
<div id="supervision-graph-axis-labels-list" style="display: none;"></div>
