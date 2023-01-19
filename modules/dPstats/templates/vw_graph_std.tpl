{{*
 * @package Mediboard\Stats
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  graph = {{$graph|@json}};

  Main.add(function () {
    graph.options.legend.container = $('display-legend-{{$type_graph}}');
    if (graph.options.mouse && graph.options.mouse.trackFormatter) {
      graph.options.mouse.trackFormatter = eval(graph.options.mouse.trackFormatter);
    }
    Flotr.draw($('display-graph-{{$type_graph}}'), graph.series, graph.options);
    {{if $can_zoom}}
    var select = DOM.select({},
      DOM.option({value: ""}, "&ndash; Vue sur un mois &ndash;")
    );

    graph.options.xaxis.ticks.each(function (tick) {
      select.insert(DOM.option({value: tick[1]}, tick[1]));
    });

    select.observe("change", function (event) {
      var date_zoom = $V(Event.element(event));

      if (!date_zoom) {
        return;
      }
      var url = Object.clone(DisplayGraph.lastUrl);
      url.addParam("type_graph", '{{$can_zoom}}');
      url.addParam("date_zoom", date_zoom);
      url.requestModal();
    });

    $('display-graph-{{$type_graph}}').down('.flotr-tabs-group').insert(select);
    {{/if}}
  });
</script>


<table class="layout">
  <tr>
    <td style="vertical-align: top;">
      <div style="width: 800px; height: 500px; float: left; margin: 1em;" id="display-graph-{{$type_graph}}"></div>
    </td>
    <td style="vertical-align: top;" id="display-legend-{{$type_graph}}"></td>
  </tr>
</table>