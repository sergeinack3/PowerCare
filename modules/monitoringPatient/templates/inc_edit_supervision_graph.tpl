{{*
 * @package Mediboard\MonitoringPatient
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    {{if $graph->_id}}
    SupervisionGraph.listAxes({{$graph->_id}});
    {{/if}}

    var item = $("list-{{$graph->_guid}}");
    if (item) {
      item.addUniqueClassName("selected", ".list-container");
    }
  });
</script>

<form name="edit-supervision-graph" method="post" onsubmit="return onSubmitFormAjax(this);">
  {{mb_class object=$graph}}
  {{mb_key object=$graph}}
  <input type="hidden" name="del" value="0" />
  <input type="hidden" name="owner_class" value="CGroups" />
  <input type="hidden" name="owner_id" value="{{$g}}" />
  <input type="hidden" name="callback" value="SupervisionGraph.callbackEditGraph" />

  <table class="main form me-margin-top-0 me-margin-bottom-0 {{if $graph->_id}}me-no-border-radius-bottom{{/if}}">
    {{mb_include module=system template=inc_form_table_header object=$graph colspan=11}}

    <tr>
      <th>{{mb_label object=$graph field=title}}</th>
      <td>{{mb_field object=$graph field=title}}</td>

      <th>{{mb_label object=$graph field=display_legend typeEnum=checkbox}}</th>
      <td>{{mb_field object=$graph field=display_legend typeEnum=checkbox}}</td>

      <th>{{mb_label object=$graph field=disabled typeEnum=checkbox}}</th>
      <td>{{mb_field object=$graph field=disabled typeEnum=checkbox}}</td>

      <th>{{mb_label object=$graph field=height}}</th>
      <td>{{mb_field object=$graph field=height form="edit-supervision-graph" increment=true}}</td>

      {{if "patientMonitoring"|module_active}}
        <th>{{mb_label object=$graph field=automatic_protocol}}</th>
        <td>{{mb_field object=$graph field=automatic_protocol typeEnum=select emptyLabel="None"}}</td>
      {{/if}}

      <td>
        <button class="modify" type="submit">{{tr}}Save{{/tr}}</button>

        {{if $graph->_id}}
          <button type="button" class="trash"
                  onclick="confirmDeletion(
                    this.form,
                    {typeName: '', objName: '{{$graph->_view|smarty:nodefaults|JSAttribute}}'},
                    SupervisionGraph.callbackEditGraph
                    )">
            {{tr}}Delete{{/tr}}
          </button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>

{{if $graph->_id}}
  <table class="main tbl me-margin-top-0 me-margin-top-bottom-0 me-no-border-radius-top me-no-border-radius-bottom">
    <tr>
      <th class="title" colspan="2">
        {{tr}}CSupervisionGraph-back-axes{{/tr}}
      </th>
    </tr>
  </table>
{{/if}}

<table class="main layout" style="height: 240px;">
  <tr>
    <td id="supervision-graph-axes-list" style="width: 40%;" class="me-padding-left-3"></td>
    <td id="supervision-graph-axis-editor" class="me-padding-right-5">&nbsp;</td>
  </tr>
</table>

<hr />

<div id="supervision-graph-preview" class="supervision"></div>