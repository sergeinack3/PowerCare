{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  graphs = {{$graphs|@json}};
  graphSizes = [
    {width: '720px', height: '500px', yaxisNoTicks: 10},
  ];

  yAxisTickFormatter = function (val) {
    return Flotr.engineeringNotation(val, 2, 1000);
  };

  drawGraphs = function (size) {
    var container;
    size = size || graphSizes[0];
    $A(graphs).each(function (g, key) {
      container = $('graph-' + key);
      container.setStyle(size);
      g.options.y2axis.noTicks = size.yaxisNoTicks;
      g.options.yaxis.noTicks = size.yaxisNoTicks;
      g.options.yaxis.tickFormatter = yAxisTickFormatter;
      g.options.y2axis.tickFormatter = yAxisTickFormatter;
      g.options.mouse = {
        track:          true,
        position:       "ne",
        relative:       true,
        sensibility:    2,
        trackDecimals:  3,
        trackFormatter: function (obj) {
          obj.y = parseFloat(obj.y);
          var decimals = Math.round(obj.y) == obj.y ? 0 : 3;
          return obj.series.label + "<br />Valeur : " + obj.y.format(decimals, 3) + "<br />Date : " + g.datetime_by_index[obj.index];
        }
      };

      Flotr.draw(container, g.series, g.options);
    });
  };
</script>

<script>
  Main.add(function () {
    drawGraphs(graphSizes[0]);
  });
</script>

{{foreach from=$graphs item=graph name=graphs}}
  <div id="graph-{{$smarty.foreach.graphs.index}}" style="width: 720px; height: 500px; margin: 20px auto;"></div>
{{/foreach}}

<!-- For styles purpose -->
<div style="clear: both;"></div>
