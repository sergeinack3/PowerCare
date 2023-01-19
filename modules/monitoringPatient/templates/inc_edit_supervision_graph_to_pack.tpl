{{*
 * @package Mediboard\MonitoringPatient
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="edit-supervision-graph-to-pack" method="post" action="?m=monitoringPatient" onsubmit="return onSubmitFormAjax(this)">
  <input type="hidden" name="m" value="monitoringPatient" />
  <input type="hidden" name="del" value="0" />
  <input type="hidden" name="@class" value="CSupervisionGraphToPack" />
  <input type="hidden" name="callback" value="SupervisionGraph.graphToPackCallback" />
  {{mb_key object=$link}}
  {{mb_field object=$link field=graph_class hidden=true}}
  {{mb_field object=$link field=pack_id hidden=true}}

  <table class="main form">
    {{mb_include module=system template=inc_form_table_header object=$link colspan=2}}

    <tr>
      <th>{{mb_label object=$link field=graph_id}}</th>
      <td>
        <select name="graph_id">
          <option value=""> &mdash;</option>
          {{foreach from=$items item=_item}}
            <option
              value="{{$_item->_id}}" {{if $_item->_class == $link->graph_class && $_item->_id == $link->graph_id}} selected {{/if}}>{{$_item}}</option>
          {{/foreach}}
        </select>
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$link field=rank}}</th>
      <td>{{mb_field object=$link field=rank form="edit-supervision-graph-to-pack" increment=true}}</td>
    </tr>
    <tr>
      <td></td>
      <td>
        <button class="modify" type="submit">{{tr}}Save{{/tr}}</button>

        {{if $link->_id}}
          <button type="button" class="trash" onclick="confirmDeletion(
            this.form,
            {typeName:'', objName:'{{$link->_view|smarty:nodefaults|JSAttribute}}'},
            SupervisionGraph.graphToPackCallback
            )">
            {{tr}}Delete{{/tr}}
          </button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>
