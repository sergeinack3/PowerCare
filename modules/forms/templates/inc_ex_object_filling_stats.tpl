{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    {{foreach from=$series key=_key item=_series}}
      plotExObject('{{$_key}}', {{$_series.series|@json}}, {{$_series.options|@json}});
    {{/foreach}}
  });

  plotExObject = function(key, series, options) {
    var ph = jQuery('#plot-exobject_' + key);
    var dates = {{$dates|@json}};

    options.legend = {
      container: jQuery('#plot-exobject-legend_' + key)
    };

    jQuery.plot(ph, series, options);

    function plotHoverExObject(event, pos, item) {
      if (item) {
        jQuery('#flot-exobject-tooltip_' + key).remove();

        var dataIndex = item.datapoint[0];
        var date = dates[dataIndex];

        var unused_fields = $T('common-msg-%d unused fields', item.series.data[item.dataIndex][1]);
        var content = printf("<strong>%s</strong><br />%s<br />%s",
          date,
          unused_fields,
          item.series.label
        );

        $$('body')[0].insert(DOM.div({className: 'tooltip', id: 'flot-exobject-tooltip_' + key}, content).setStyle({
          position:        'absolute',
          top:             pos.pageY + 5 + 'px',
          left:            pos.pageX + 5 + 'px',
          opacity:         0.8,
          backgroundColor: '#000000',
          color:           '#FFFFFF',
          borderRadius:    '4px',
          textAlign:       'center',
          maxWidth:        '300px',
          whiteSpace:      'normal'
        }));
      }
      else {
        jQuery('#flot-exobject-tooltip_' + key).remove();
      }
    }

    $(ph).bind('plothover', plotHoverExObject);
  };
</script>

<table class="main layout">
  {{foreach from=$series key=_key item=_series}}
    <tr>
      <td style="width: 90%">
        <fieldset class="me-no-box-shadow">
          <legend>{{tr}}CExClass-legend-Stats {{$_key}}{{/tr}}</legend>

          {{if $_key == 'target'}}
            <div id="plot-exobject_{{$_key}}" style="max-width: 100%; height: 300px;"></div>
          {{else}}
            <div id="plot-exobject_{{$_key}}" style="max-width: 100%; height: 500px;"></div>
          {{/if}}
        </fieldset>
      </td>

      <td>
        <fieldset>
          <legend>{{tr}}common-Legend{{/tr}}</legend>

          <div id="plot-exobject-legend_{{$_key}}"></div>
        </fieldset>
      </td>
    </tr>
  {{/foreach}}
</table>