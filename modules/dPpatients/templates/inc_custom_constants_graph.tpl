{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=patients script=constants_graph ajax=true}}
{{mb_default var=msg value=0}}
{{mb_default var=patient value=0}}

{{if $msg}}
  <div class="small info">
    {{$msg}}
  </div>
{{else}}
  <style xmlns="http://www.w3.org/1999/html">
    .graph-legend {
      vertical-align: top;
      line-height: 1;
      padding-left: 1em !important;
      padding-top: 0.2em !important;
    }
  </style>
  <script type="text/javascript">
    {{if $patient}}
      printGraph = function() {
        new Url('patients', 'ajax_custom_constants_graph')
          .addParam('patient_id', '{{$patient->_id}}')
          .addParam('constants', '{{$constants|html_entity_decode}}')
          .addParam('print', '1')
          .pop();
      }
    {{/if}}

    Main.add(function () {
      var graphs_data = {{$graphs|@json}};
      window.oGraphs = new ConstantsGraph(graphs_data, {{$min_x_index}}, {{$min_x_value}}, true);
      window.oGraphs.draw();

      {{if $print}}
        setTimeout(
          function() {
            window.print();
            setTimeout(window.close, 250);
          },
          250
          );
      {{/if}}

    });
  </script>
  {{if $patient}}
    <table class="tbl">
      <tr>
        <th class="title">
          {{$patient->_view}} - {{mb_value object=$patient field=naissance}}
          <button type="button" class="not-printable print notext" style="float:right" onclick="printGraph()">
            {{tr}}Print{{/tr}}
          </button>
        </th>
      </tr>
    </table>
  {{/if}}

  {{foreach from=$graphs item=_graph key=_id}}
    <div id="tab-{{$_id}}">
      <table class="layout">
        <tr>
          <td class="me-padding-left-20">
            <div id="placeholder_{{$_id}}" style="width: 800px; height: 250px; margin-bottom: 5px;"></div>
          </td>
          <td id="legend_{{$_id}}" class="graph-legend"></td>
        </tr>
      </table>
    </div>
  {{/foreach}}
{{/if}}
