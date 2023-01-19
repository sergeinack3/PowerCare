{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  var graphs = {{$graphs|@json}};
  
  function drawGraphs() {
    $A(graphs).each(function(g, key){
      {{if $element == "_average_duration" || $element == "_average_request"}}
      g.options.pie.labelFormatter = function(obj) {return Math.round(obj.value*1000) + "ms"};
      {{/if}}
      {{if $element == "_average_nb_requests"}}
      g.options.pie.labelFormatter = function(obj) {return Math.round(obj.value) + " rq"};
      {{/if}}
      Flotr.draw($('graph-'+key), g.series, g.options);
    });
  }
  
  Main.add(function () {
    drawGraphs();
  });
</script>

<tr>
  <td>
    {{if $groupres == 1}}
      <div id="graph-0" style="float: left; width: 600px; height: 400px; border: 1px solid black;"></div>
      <div id="graph-1" style="float: left; width: 600px; height: 400px; border: 1px solid black;"></div>
    {{else}}
      {{foreach from=$graphs item=graph name=graphs}}
        <div id="graph-{{$smarty.foreach.graphs.index}}" style="float: left; width: 600px; height: 400px;"></div>
      {{/foreach}}
    {{/if}}
  </td>
</tr>
