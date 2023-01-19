{{*
 * @package Mediboard\MonitoringPatient
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main tbl">
  <tr>
    <th class="narrow">{{tr}}common-Action{{/tr}}</th>
    <th>{{tr}}CSupervisionGraphPack-back-graph_links{{/tr}}</th>
    <th class="narrow">{{tr}}CSupervisionGraphToPack-rank{{/tr}}</th>
  </tr>
  {{foreach from=$pack->_ref_graph_links item=_link name=list_graph_links}}
    {{assign var=graph value=$_link->_ref_graph}}
    <tr class="{{if $graph->disabled}}hatching{{/if}}">
      <td class="narrow">
        <button class="edit compact notext me-tertiary" onclick="SupervisionGraph.editGraphToPack({{$_link->_id}})">
          {{tr}}Edit{{/tr}}
        </button>
        {{if $_link->graph_class == "CSupervisionTimedData"}}
          <img src="images/icons/text.png" title="{{tr}}CSupervisionTimedData{{/tr}}" />
        {{elseif $_link->graph_class == "CSupervisionGraph"}}
          <img src="images/icons/chart.png" title="{{tr}}CSupervisionGraph{{/tr}}" />
        {{elseif $_link->graph_class == "CSupervisionTimedPicture"}}
          <img src="images/icons/image.png" title="{{tr}}CSupervisionTimedPicture{{/tr}}" />
        {{elseif $_link->graph_class == "CSupervisionInstantData"}}
          <img src="images/icons/info.png" title="{{tr}}CSupervisionInstantData{{/tr}}" />
        {{elseif $_link->graph_class == "CSupervisionTable"}}
          <img src="images/icons/order.png" title="{{tr}}CSupervisionTable{{/tr}}" />
        {{/if}}
      </td>
      <td>
        <span onmouseover="ObjectTooltip.createEx(this, '{{$graph->_guid}}');">
          {{$graph}}
        </span>
      </td>
      <td class="narrow">
        <div>
          <div style="display: inline-block;">
            {{mb_field object=$_link field=rank onchange="SupervisionGraph.changeRank('`$_link->_id`', this.value, null, '`$_link->pack_id`');" style="width:50px;" class="me-small"}}
          </div>
          <div style="display: inline-block; margin-left: 20px;">
              {{if $smarty.foreach.list_graph_links.first}}
                <i class="fas fa-sort-down graph-rank" title="{{tr}}CSupervisionGraphToPack-action-Get off 1 row{{/tr}}"
                   onclick="SupervisionGraph.changeRank('{{$_link->_id}}', '{{$_link->rank}}', 'down', '{{$_link->pack_id}}');"></i>
              {{elseif $smarty.foreach.list_graph_links.last}}
                <i class="fas fa-sort-up graph-rank" title="{{tr}}CSupervisionGraphToPack-Go up 1 rank{{/tr}}"
                   onclick="SupervisionGraph.changeRank('{{$_link->_id}}', '{{$_link->rank}}', 'up', '{{$_link->pack_id}}');"></i>
              {{else}}
                <span style="display: block;">
              <i class="fas fa-sort-up graph-rank up-rank-graph" title="{{tr}}CSupervisionGraphToPack-Go up 1 rank{{/tr}}"
                 onclick="SupervisionGraph.changeRank('{{$_link->_id}}', '{{$_link->rank}}', 'up', '{{$_link->pack_id}}');"></i>
            </span>
                <span style="display: block;">
              <i class="fas fa-sort-down graph-rank down-rank-graph" title="{{tr}}CSupervisionGraphToPack-action-Get off 1 row{{/tr}}"
                 onclick="SupervisionGraph.changeRank('{{$_link->_id}}', '{{$_link->rank}}', 'down', '{{$_link->pack_id}}');"></i>
            </span>
              {{/if}}
          </div>
        </div>
      </td>
    </tr>
  {{foreachelse}}
    <tr>
      <td colspan="4" class="empty">{{tr}}CSupervisionGraphToPack.none{{/tr}}</td>
    </tr>
  {{/foreach}}
  <tr>
    <td colspan="4">
      <button class="compact" onclick="SupervisionGraph.editGraphToPack(0, '{{$pack->_id}}', 'CSupervisionGraph')">
        <img src="images/icons/chart.png" title="{{tr}}CSupervisionGraph{{/tr}}" />
        {{tr}}CSupervisionGraph{{/tr}}
      </button>
      <button class="compact" onclick="SupervisionGraph.editGraphToPack(0, '{{$pack->_id}}', 'CSupervisionTimedData')">
        <img src="images/icons/text.png" title="{{tr}}CSupervisionTimedData{{/tr}}" />
        {{tr}}CSupervisionTimedData{{/tr}}
      </button>
      <button class="compact" onclick="SupervisionGraph.editGraphToPack(0, '{{$pack->_id}}', 'CSupervisionTimedPicture')">
        <img src="images/icons/image.png" title="{{tr}}CSupervisionTimedPicture{{/tr}}" />
        {{tr}}CSupervisionTimedPicture{{/tr}}
      </button>
      <button class="compact" onclick="SupervisionGraph.editGraphToPack(0, '{{$pack->_id}}', 'CSupervisionTable')">
        <img src="images/icons/order.png" title="{{tr}}CSupervisionTable{{/tr}}" />
        {{tr}}CSupervisionTable{{/tr}}
      </button>

      {{if "patientMonitoring"|module_active}}
        <button class="compact" onclick="SupervisionGraph.editGraphToPack(0, '{{$pack->_id}}', 'CSupervisionInstantData')">
          <img src="images/icons/info.png" title="{{tr}}CSupervisionInstantData{{/tr}}" />
          {{tr}}CSupervisionInstantData{{/tr}}
        </button>
      {{/if}}
    </td>
  </tr>
</table>