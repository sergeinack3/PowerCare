{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  graphs = {{$graphs|@json}};
  graphSizes = [
    {width: '360px', height: '250px', yaxisNoTicks: 5},
    {width: '720px', height: '500px', yaxisNoTicks: 10}
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
          var decimals = Math.round(obj.y) == obj.y ? 0 : 3
          return obj.series.label + "<br />Valeur : " + obj.y.format(decimals, 3) + "<br />Date : " + g.datetime_by_index[obj.index];
        }
      };
      var f = Flotr.draw(container, g.series, g.options);

      {{if $groupmod==1}} // explore module actions
      f.overlay.setStyle({cursor: 'pointer'})
        .observe('click', function (m) {
          return function () {
            $V(getForm('typevue').groupmod, m)
          }
        }(g.module));
      {{else}} // open contextual long request log
      var data_hit;
      var data;
      container.setStyle({cursor: 'pointer'})
        .observe('flotr:hit', function (event) {
          data_hit = event.memo;
        })
        .observe('flotr:click', function (event) {
          if (data_hit) {
            var data_index = data_hit[0].index;
            var from = Date.fromDATETIME(g.datetime_by_index[data_index]);

            var to = new Date;
            to.setTime(from.getTime());
            switch ('{{$interval}}') {
              case "one-day":
                to.addMinutes(10);
                break;

              case "one-week":
                to.addHours(1);
                break;

              case "eight-weeks":
                to.addDays(1);
                break;

              case "one-year":
                to.addDays(7);
                break;

              case "four-years":
                to.addMonths(1);
                break;

              case "twenty-years":
                to.addYears(1);
                break;
            }

            showLongRequestLogs(from, to);
          }
        });
      {{/if}}
    });
  };

  showLongRequestLogs = function (from, to) {
    var url = new Url('system', 'ajax_stats_long_request_logs');
    url.addParam('from', from.toDATETIME(true));
    url.addParam('to', to.toDATETIME(true));
    url.addParam('from_access_logs', 1);
    url.addParam('user_type', '{{$user_type}}');
    url.addParam('module', '{{$module}}');
    url.requestModal('50%','50%');
  }
</script>

<script>
  Main.add(function () {
    drawGraphs(graphSizes[{{if $bigsize}}1{{else}}0{{/if}}]);
  });
</script>

{{foreach from=$graphs item=graph name=graphs}}
  <div id="graph-{{$smarty.foreach.graphs.index}}" style="width: 380px; height: 250px; float: left; margin: 10px 2px;"></div>
{{/foreach}}

{{if $groupmod == 2}}
  <div id="vw_long_request_logs" style="width: 380px; height: 250px; float: left; margin: 10px 2px;"></div>
{{/if}}

<!-- For styles purpose -->
<div style="clear: both;"></div>
