{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  GraphPrint = function (select) {
    var url = new Url('patients', 'ajax_courbe_reference_graph');
    url.addParam('graph_name', select);
    url.addParam('graph_print', 1);
    url.popup(600, 550, "courbe_reference");
  }

  Main.add(function () {
    drawGraph();

    {{if $graph_print}}
    window.print();
    window.setTimeout(function () {
      this.close();
    }, 250);
    {{/if}}
  });

  drawGraph = function () {
    var oData = {{$graph_axes|@json}};
    var oPh = jQuery("#placeholder_{{$graph_name}}");
    if('{{$graph_name}}' == 'bilirubine_transcutanee') {
      data = CourbeReference.drawBTC({{$Data|@json}});
      oData = oData.concat(data);
    }
    else{
      oData = oData.concat({{$Data|@json}});
    }
    oPh.bind('plothover', plotHover);

    var plot = jQuery.plot(oPh, oData, {
      series:     {
        curvedLines: {
          active: true
        },
        bandwidth : {
          active : true
        }
      },
      yaxis:      {
        labelWidth:    30,
        minorTickFreq: 5,
        tickSize: {{$yTickStep}},
        min: {{$yMin}},
        max: {{$yMax}}
      },
      xaxis:      {
        labelHeight:     30,
        min: {{$xMin}},
        max: {{$xMax}},
        tickSize: {{$xTickStep}}
        {{if $xTickFormat}}
        , tickFormatter: function (v) {
          return Math.round(v / 12)
        }
        {{/if}}
      },
      grid:       {
        hoverable: true
      },
      legend:     {
        container: '#legend_container',
        show:      true,
        noColumns: 10
      },
      shadowSize: 0
    });
    if('{{$graph_name}}' == 'bilirubine_transcutanee') {
      data[0].data.each(function (data) {
        var max = Math.max(data[1], data[2]);
        var min = Math.min(data[1], data[2]);
        var oPointMax = plot.pointOffset({x: data[0], y: max, yaxis: data[0].yaxis});
        var oPointMin = plot.pointOffset({x: data[0], y: min, yaxis: data[0].yaxis});

        oPh.append('<div style="position: absolute; left: ' + (oPointMax.left - 8) + 'px; top: ' + (oPointMax.top - 15) + 'px; font-size: smaller">' + max + '</div>');
        oPh.append('<div style="position: absolute; left: ' + (oPointMin.left - 8) + 'px; top: ' + (oPointMin.top + 5) + 'px; font-size: smaller">' + min + '</div>');
      });
    }
    $('placeholder_{{$graph_name}}').insert(DOM.div({class: 'axisLabel yaxisLabel'}, $T('{{$unit}}')));
    $('placeholder_{{$graph_name}}').insert(DOM.div({class: 'axisLabel xaxisLabel'}, $T('{{$type_age}}')));
  };

  plotHover = function (event, pos, item) {
    jQuery("#flot-tooltip").remove();

    if (item) {
      var age = item.datapoint[0];
      var type_age = '{{$type_age}}';
      var unit = '{{$unit}}';

      var content = '<strong>' + item.datapoint[1] + unit + ' à ';
      if (type_age === 'ans') {
        var months = null;
        age = Math.floor(age);

        months = Math.floor((item.datapoint[0] - Math.floor(item.datapoint[0])) * 12);

        content += (age > 0) ? age + ' ans' : '';
        content += (age > 0 && months > 0) ? ' et ' : '';
        content += (months > 0) ? months + ' mois' : '';
        content += '</strong>';
      }
      if (type_age === 'mois') {
        age = Math.floor(age);
        var weeks = Math.floor((item.datapoint[0] - Math.floor(item.datapoint[0])) * 7);

        content += (age > 0) ? age + ' mois' : '';
        content += (age > 0 && weeks > 0) ? ' et ' : '';
        content += (weeks > 0) ? weeks + ' semaines' : '';
        content += '</strong>';
      }
      if (type_age === 'heure') {
        content += age + " heures";
      }

      $$("body")[0].insert(DOM.div({className: "tooltip", id: "flot-tooltip"}, content).setStyle({
        top:  pos.pageY + 5 + "px",
        left: pos.pageX + 5 + "px"
      }));
    }
  };
</script>

<table class="main layout">
  <tr>
    <td colspan="2">
      <h2 style="text-align: center">
        <strong>{{tr}}CCourbeReference-{{$growthCurveName}}{{/tr}}</strong>
      </h2>
    </td>
  </tr>
  <tr>
    <td style="text-align: center;">
      <div class="placeholder_mater" id="placeholder_{{$graph_name}}" style="width: 950px; height: 550px;"></div>
    </td>
  </tr>
  <tr>
    {{if $graph_name != "bilirubine_transcutanee" && $graph_name != "bilirubine_totale_sanguine"}}
      <td colspan="2">
        <p style="margin-top: 10px; color: #4d4d4d; text-align: center;">
          {{tr}}common-Source{{/tr}} : Étude séquentielle française de la croissance CIE-INSERM. (M. Sempé)
          <br />
          {{if $unit == "kg" || $unit == "kg/m²"}}
            Variations en centiles
          {{else}}
            Variations en écarts-types (&sigma;) [1 &sigma; = 1DS]
          {{/if}}
        </p>
      </td>
    {{/if}}
  </tr>

  <tr>
    <td style="text-align: center">
      <fieldset id="legend_container" style="margin-right: 20px; display: inline; vertical-align: top"></fieldset>
    </td>
  </tr>
  <tr>
    <td style="text-align: center">
      {{if !$graph_print}}
        <button type="button" id="printer" class="print"
                onclick="GraphPrint('{{$graph_name}}'); return false;"
                style="vertical-align: top">{{tr}}Print{{/tr}}</button>
      {{/if}}
    </td>
  </tr>

</table>

