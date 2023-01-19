{{*
* @package Mediboard\System
* @author  SAS OpenXtrem <dev@openxtrem.com>
* @license https://www.gnu.org/licenses/gpl.html GNU General Public License
* @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<style>
  .truncate {
    width: 150px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    font-size: x-small;
    text-align: center;
    padding: 2px;
  }

  .truncate:hover {
    overflow: visible;
  }
</style>

<script>
  Main.add(function () {
    drawGraphs();
  });

  drawGraphs = function () {
      {{foreach from=$graphs key=_id item=_graph}}
      {{if $_graph.total}}
    var oData = {{$_graph.data|@json}};
    var oOptions = {{$_graph.options|@json}};

    oOptions.series.pie.label.formatter = function (label, series) {
      label = label.split(' - ');

      var _module = label[0];
      var _action = label[1] || null;

      if (_action) {
        return printf("<div class='truncate' style='text-align: center; color:%s;'>%s<br />%s<br />%s</div>", series.color, _module, _action, series.data[0][1]+'%');
      } else {
        return printf("<div class='truncate' style='text-align: center; color:%s;'>%s<br />%s</div>", series.color, _module, series.data[0][1]+'%');
      }
    };

    var oPh = jQuery("#placeholder_{{$_id}}");
    oPh.bind('plothover', plotHover);
    var plot = jQuery.plot(oPh, oData, oOptions);
    {{/if}}
    {{/foreach}}
  };

  plotHover = function (event, pos, item) {
    if (item) {
      jQuery("#flot-tooltip").remove();

      var label = item.series.label.split(' - ');

      var _module = label[0];
      var _action = label[1] || null;

      if (_action) {
        content = printf("<strong>%s<br />%s</strong><br />%s", _module, _action, item.series.data[0][1]+'%');
      } else {
        content = printf("<strong>%s</strong><br />%s", _module, item.series.data[0][1]+'%');
      }

      $$("body")[0].insert(DOM.div({className: "tooltip", id: "flot-tooltip"}, content).setStyle({
        top:  pos.pageY + 5 + "px",
        left: pos.pageX + 5 + "px"
      }));
    } else {
      jQuery("#flot-tooltip").remove();
    }
  };
</script>

<table class="main layout">
  <tr>
    <td style="text-align: center;">
        {{foreach from=$graphs key=_id item=_graph}}
        {{if $_graph.total || true}}
      <div id="long_request_logs_graph_{{$_id}}" style="display: inline-block; border: solid 1px #ccc;">
        <table class="layout">
          <tr>
            <td>
              <p style="text-align: center;">
                <strong>
                    {{if $interval == 'day'}}
                    {{if $_graph.dates}}
                    {{$_graph.dates}}
                    {{else}}
                    {{$date|date_format:$conf.longdate}}
                  {{/if}}
                    {{else}}
                    {{$min_date|date_format:$conf.datetime}} &ndash; {{$max_date|date_format:$conf.datetime}}
                  {{/if}} &ndash;

                    {{$_graph.title|smarty:nodefaults}} &ndash; {{$_graph.total}} {{tr}}common-second|pl{{/tr}}

                    {{if $user_type === '1'}}
                       - Humains
                    {{elseif $user_type === '2'}}
                       - Robots
                    {{elseif $user_type === '0'}}
                       - Tous type d'utilisateur
                  {{/if}}
                </strong>
              </p>

                {{if $group_mod == '2'}}
              <div id="placeholder_{{$_id}}" style="width: 500px; height: 300px;"></div>
                {{else}}
              <div id="placeholder_{{$_id}}" style="width: 700px; height: 500px;"></div>
              {{/if}}
            </td>
          </tr>
        </table>
      </div>
        {{else}}
      <div style="display: inline-block;" class="empty">{{tr}}CLongRequestLog.none{{/tr}}</div>
      {{/if}}
      {{/foreach}}
    </td>
  </tr>
</table>
