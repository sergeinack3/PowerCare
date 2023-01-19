{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function() {
    plotParams();
  });

  plotParams = function() {
    var ph = jQuery("#plot-param");
    var ticks = {{$param_ticks|@json}};
    var series = {{$param_series|@json}};
    var totals = {{$param_totals|@json}};
    var options = {{$param_options|@json}};
    options.legend = {
      container: jQuery("#plot-param-legend")
    };

    jQuery.plot(ph, series, options);

    function plotHover(event, pos, item) {
      if (item) {
        jQuery("#flot-tooltip").remove();

        var dataIndex = item.datapoint[0];

        var content = printf(
          "<strong>%s</strong><br />%d / %d au total<br />%s",
          ticks[dataIndex],
          item.series.data[item.dataIndex][1],
          totals[dataIndex],
          item.series.label
        );

        $$("body")[0].insert(DOM.div({className: "tooltip", id: "flot-tooltip"}, content).setStyle({
          top:  pos.pageY + 5 + "px",
          left: pos.pageX + 5 + "px"
        }));
      }
      else {
        jQuery("#flot-tooltip").remove();
      }
    }

    $(ph).bind("plothover", plotHover);
  };
</script>

<table class="main layout">
  <tr>
    <td class="narrow">
      <div id="plot-param" style="width: 800px; height: 300px;"></div>
    </td>

    <td>
      <div id="plot-param-legend"></div>

      <table class="main tbl">
        <tr>
          <th>{{tr}}CExClass{{/tr}}</th>
          <th class="text">{{tr}}CExClassField-msg-total{{/tr}}</th>
          <th class="text">{{tr}}CExClassField-msg-disabled{{/tr}}</th>
          <th class="text">{{tr}}CExClassField-msg-reported{{/tr}}</th>
        </tr>

        {{foreach from=$param_forms_total item=_form}}
          {{assign var=_ex_class_id value=$_form.ex_class_id}}
          <tr>
            <td>
                <span onmouseover="ObjectTooltip.createEx(this, 'CExClass-{{$_ex_class_id}}');">
                  {{$_form.name|truncate:50}}
                </span>
            </td>

            <td>{{$_form.total}}</td>

            <td>
              {{if array_key_exists($_form.ex_class_id,$param_forms_disabled)}}
                {{$param_forms_disabled.$_ex_class_id.total}}
              {{/if}}
            </td>

            <td>
              {{if array_key_exists($_form.ex_class_id,$param_forms_reported)}}
                {{$param_forms_reported.$_ex_class_id.total}}
              {{/if}}
            </td>
          </tr>
        {{/foreach}}
      </table>
    </td>
  </tr>
</table>