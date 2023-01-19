{{*
 * @package Mediboard\MonitoringPatient
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
  Main.add(function () {
    var ph = jQuery("#placeholder-preview");
    var series = {{$data.series|@json}};
    var xaxes = {{$data.xaxes|@json}};

    xaxes[0].ticks = 10;

    var plothover = function (event, pos, item) {
      if (item) {
        var key = item.dataIndex + "-" + item.seriesIndex;
        var yaxis = item.series.yaxis.n;

        ph[0].select(".flot-y" + yaxis + "-axis, .flot-y" + yaxis + "-axis .flot-tick-label").invoke("addClassName", "axis-onhover");

        if (window.previousPoint != key) {
          window.previousPoint = key;
          jQuery("#flot-tooltip").remove();

          var contents = SupervisionGraph.formatTrack(item);

          $$("body")[0].insert(DOM.div({className: "tooltip", id: "flot-tooltip"}, contents).setStyle({
            position: 'absolute',
            top:      item.pageY + 5 + "px",
            left:     item.pageX + 5 + "px"
          }));
        }
      } else {
        $$(".axis-onhover").invoke("removeClassName", "axis-onhover");

        jQuery("#flot-tooltip").remove();
        window.previousPoint = null;
      }
    };

    ph.bind("plothover", plothover);

    jQuery.plot(ph, series, {
      grid:   {
        hoverable:       true,
        margin:          0,
        tickMargin:      0,
        labelMargin:     2,
        minBorderMargin: 0,
        borderWidth:     1
      },
      legend: {
        show: {{$graph->display_legend|ternary:'true':'false'}},
        container: "#legend-preview",
        noColumns: 5,
        margin:    0,
        sorted:    "reverse"
      },
      series: SupervisionGraph.defaultSeries,
      xaxes:  xaxes,
      yaxes: {{$data.yaxes|@json}}
    });
  });
</script>

<button onclick="SupervisionGraph.preview({{$supervision_graph_id}})" class="change">Rafraîchir l'aperçu</button>

<div style="margin: 6px;">
  <div class="yaxis-labels" style="height:{{$graph->height}}px;">
    {{foreach from=$data.yaxes|@array_reverse item=_yaxis name=_yaxis}}
      <div class="axis" style="color: {{$_yaxis.color}};">
        {{$_yaxis.label}}
        <div class="symbol">{{$_yaxis.symbolChar|smarty:nodefaults}}</div>
      </div>
    {{/foreach}}

    {{if $graph->display_legend}}
      <div id="legend-preview" class="graph-legend" style="position: absolute; bottom: 3px;"></div>
    {{/if}}
  </div>
  <div id="placeholder-preview" style="width:750px;height:{{$graph->height}}px;"></div>
</div>
