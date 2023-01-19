{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  detailBilanHydrique = function (datetime) {
    new Url("patients", "ajax_detail_bilan_hydrique")
      .addParam("sejour_id", "{{$sejour->_id}}")
      .addParam("datetime", datetime)
      .addParam('granularite', '{{$granularite}}')
      .requestModal("60%");
  };

  plotClick = function (event, pos, item) {
    if (!item) {
      return;
    }

    var index = item.dataIndex;
    var datetime = item.series.xaxis.ticks[index].label;

    datetime = Date.fromLocaleDateTime(datetime.replace(/<br \/>/, " ")).toDATETIME();

    detailBilanHydrique(datetime);
  };

  Main.add(function () {
    var form = getForm('changeDateBilan');

    ViewPort.SetAvlHeight('placeholder', 0.9);
    ViewPort.SetAvlWidth('placeholder', 0.9);

    Calendar.regField(
      form.date,
      {limit:
          {
            start: '{{$sejour->entree|date_format:"%Y-%m-%d"}}',
            stop:  '{{$sejour->sortie|date_format:"%Y-%m-%d"}}'
          }
      },
      {noView: true}
    );

    var options = {{$graph.options|@json}};
    options.legend = {container: jQuery("#legend")};
    var data = {{$graph.data|@json}};

    var ph = jQuery("#placeholder");
    ph.bind("plotclick", plotClick);

    var plot = jQuery.plot(ph, data, options);

    data.each(function (serie) {
      if (serie.bars) {
        serie.data.each(function (data) {
          if (data[1] != 0 && data[1] != null) {
            var top = 5;
            if (data[1] < 0) {
              top = -10;
            }
            var oPoint = plot.pointOffset({x: data[0], y: data[1]});
            ph.append('<div style="position: absolute; left:' + (oPoint.left + 5) + 'px; top: ' + (oPoint.top + top) + 'px; font-size: smaller">' + data[1] + '</div>');
          }
        }.bind(this));
      }
    });
  });
</script>

{{mb_include module=soins template=inc_patient_banner object=$sejour patient=$sejour->_ref_patient}}

<table class="layout" style="width: 100%;">
  <tr>
    <th>
      <button type="button" class="left" style="float: left;"
        {{if $date_bh_before >= $sejour->_date_entree}}
        onclick="Control.Modal.close(); resumeBilanHydrique('{{$date_bh_before}}');"
        {{else}}
      disabled
        {{/if}}>
        {{$date_bh_before|date_format:$conf.date}}
      </button>
      <button type="button" class="right" style="float: right;"
        {{if $date_bh_after <= $sejour->_date_sortie}}
              onclick="Control.Modal.close(); resumeBilanHydrique('{{$date_bh_after}}');"
      "
      {{else}}
      disabled
      {{/if}}>
      {{$date_bh_after|date_format:$conf.date}}
      </button>

      <h3>
        {{$date_bh|date_format:$conf.date}}

        <form name="changeDateBilan" method="get">
          <input type="hidden" name="date" value="{{$date_bh}}" onchange="Control.Modal.close(); resumeBilanHydrique(this.value);" />
        </form>
      </h3>
    </th>
    <th></th>
  </tr>
  <tr>
    <td style="text-align: center; width: 80%" rowspan="2">
      <div id="placeholder" style="margin-left: auto;"></div>
    </td>
    <td style="vertical-align: top; height: 1%;">
      {{tr}}CConstantesMedicales-Granularite{{/tr}}
      <select name="granularite" onchange="Control.Modal.close(); resumeBilanHydrique(null, this.value);">
        <option value="2" {{if $granularite == 2}}selected{{/if}}>2h</option>
        <option value="4" {{if $granularite == 4}}selected{{/if}}>4h</option>
        <option value="8" {{if $granularite == 8}}selected{{/if}}>8h</option>
        <option value="12" {{if $granularite == 12}}selected{{/if}}>12h</option>
        <option value="24" {{if $granularite == 24}}selected{{/if}}>24h</option>
      </select>
    </td>
  </tr>
  <tr>
    <td>
      <div id="legend"></div>
    </td>
  </tr>
</table>