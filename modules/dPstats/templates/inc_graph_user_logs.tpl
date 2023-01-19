{{*
 * @package Mediboard\Stats
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  yAxisTickFormatter = (val) => {
    return Flotr.engineeringNotation(val, 2, 1000);
  };

  var graph = {{$graph|@json}};

  drawGraphs = () => {
    var size = {
      width:  '900px',
      height: '500px'
    };

    var container = $('graph');
    container.setStyle(size);

    graph.options.yaxis.noTicks = 10;
    graph.options.yaxis.tickFormatter = yAxisTickFormatter;
    Flotr.draw(container, graph.series, graph.options);
  };

  Main.add(function () {
    drawGraphs();
  });
</script>

<div id="graph" class="me-margin-auto"></div>

