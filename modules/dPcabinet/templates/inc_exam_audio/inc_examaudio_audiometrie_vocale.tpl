{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    var data = {{$graph->series|@json}};
    {{if $old_consultation_id}}
    data_old = {{$graph_old->series|@json}};
    data_old.forEach(function (serie) {
      if (serie["label"]) {
        switch (serie.label) {
          case "Oreille gauche" :
            serie.color = "#B1D4FF";
            break;
          case "Oreille droite" :
            serie.color = "#FFB9B9";
            break;
          case "Courbe optimale" :
            serie.color = "#D3D3D3";
            break;
        }
        serie.label = serie.label.concat(" - ", '{{$old_consultation->_date|date_format:$conf.longdate}}');
      }
    });
    data = data_old.concat(data);
    {{/if}}

    var options = {{$graph->options|@json}};
    
    options.xaxis.tickFormatter = function (val) {
      return val + "dB";
    };
    
    options.yaxis.tickFormatter = function (val) {
      return val + "%";
    };
    
    options.legend.container = "#{{$graph->getId()}}-legend";
    // Draw second x axis
    options.hooks = {
      drawSeries: [function (plot, ctx, series) {
        if (series.type !== "axis") {
          return;
        }
        var xaxis = series.xaxis;
        var yaxis = series.yaxis;
        var plotOffset = plot.getPlotOffset();
        
        ctx.save();
        ctx.font = "10px sans-serif";
        ctx.fillStyle = "#666";
        ctx.strokeStyle = "#666";
        
        series.data.each(function (d, i) {
          ctx.moveTo(xaxis.p2c(d[0]) + plotOffset.left, yaxis.p2c(d[1]) - 3 + plotOffset.top);
          ctx.lineTo(xaxis.p2c(d[0]) + plotOffset.left, yaxis.p2c(d[1]) + 3 + plotOffset.top);
          
          var label = "" + i * 10;
          
          ctx.fillText(label, xaxis.p2c(d[0] - label.length) + plotOffset.left, yaxis.p2c(d[1] + 2) + plotOffset.top);
        });
        
        ctx.moveTo(xaxis.p2c(10 - 1) + plotOffset.left, yaxis.p2c(50) + plotOffset.top);
        ctx.lineTo(xaxis.p2c(110 + 1) + plotOffset.left, yaxis.p2c(50) + plotOffset.top);
        
        ctx.stroke();
        ctx.restore();
      }]
    };

    var ph = jQuery("#{{$graph->getId()}}");
    {{if $exam_audio->_can->edit}}
      ph.bind("plotclick", function (event, pos, item) {
        if (item && ($V(getForm("editFrm")._oreille) == item.series.side && !item.series.label.includes("-"))) {
          //Ouverture de modale de modification
          ExamAudio.changeVocalValue(item.series.side, item.datapoint[0], null, null, {{if $old_consultation_id}}{{$old_exam_audio->_id}}{{/if}});
        } else {
          //Création de point (modifie ceux de la même absisse)
          ExamAudio.changeVocalValue(null, null, pos.x, pos.y, {{if $old_consultation_id}}{{$old_exam_audio->_id}}{{/if}});
        }
      });
    {{/if}}
    ph.bind("plothover", function (event, pos, item) {
      jQuery("#flot-tooltip").remove();
      if (item && ($V(getForm("editFrm")._oreille) == item.series.side && !item.series.label.includes("-"))) {
        var content = "Modifier le valeur #{pc}% à #{dB}dB pour l'oreille #{cote}".interpolate({
          dB:   item.datapoint[0],
          pc:   item.datapoint[1],
          cote: item.series.side
        });

        $$("body")[0].insert(DOM.div({className: "tooltip", id: "flot-tooltip"}, content).setStyle({
          top:  pos.pageY + 5 + "px",
          left: pos.pageX + 5 + "px"
        }));
      }
    });
    
    var plot = jQuery.plot(ph, data, options);

    ph[0].store("plot", plot);
  });
</script>

<table class="layout" style="margin: auto !important;">
  <tr>
    <td>
      <div id="{{$graph->getId()}}" style="width: 450px; height: 305px; display: inline-block;"></div>
    </td>
    <td id="{{$graph->getId()}}-legend"></td>
  </tr>
</table>
