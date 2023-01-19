{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=patients script=constants_graph ajax=true}}

<style xmlns="http://www.w3.org/1999/html">
  .graph-legend {
    vertical-align: top;
    line-height: 1;
    padding-left: 1em !important;
    padding-top: 0.2em !important;
  }

</style>

<ul id="tab-constantes-widget" class="control_tabs small" style="font-size: 0.8em;">
  {{foreach from=$graphs item=_graph key=_id}}
    <li><a href="#tab-{{$_id}}">{{$graphs_titles.$_id}}</a></li>
  {{/foreach}}
</ul>

{{foreach from=$graphs item=_graph key=_id}}
  <div id="tab-{{$_id}}">
    <table class="layout">
      <tr>
        <td>
          <div class="me-color-black-high-emphasis" id="placeholder_{{$_id}}" style="width: 350px; height: 120px;"></div>
        </td>
        <td id="legend_{{$_id}}" class="graph-legend "></td>
      </tr>
    </table>
  </div>
{{/foreach}}

<script>
  Main.add(function () {

    var graphs_data = {{$graphs|@json}};
    window.oGraphs = new ConstantsGraph(graphs_data, {{$min_x_index}}, {{$min_x_value}}, true);
    window.oGraphs.draw();
    Control.Tabs.create("tab-constantes-widget");
  })
</script>