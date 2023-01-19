{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  graph = {{$graph|@json}};

  Main.add(function () {
    graph.options.legend.container = $('display-legend');
    if (graph.options.mouse) {
      graph.options.mouse.trackFormatter = eval(graph.options.mouse.trackFormatter);
    }
    Flotr.draw($('display-graph'), graph.series, graph.options);
  });
</script>


<table class="layout">
  <tr>
    <td style="vertical-align: top;">
      <div style="width: 800px; height: 500px; float: left; margin: 1em;" id="display-graph"></div>
    </td>
    <td style="vertical-align: top;" id="display-legend"></td>
  </tr>
</table>