{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function(){
    var data = {{$graph->series|@json}};
    {{if $old_consultation_id}}
      data_old = {{$graph_old->series|@json}};
      data_old.forEach(function(serie) {
        switch (serie.points.symbol) {
          case "cross" :
            serie.color = "#B1D4FF";
            break;
          case "circle" :
            serie.color = "#FFB9B9";
            break;
        }
    });
      data = data_old.concat(data);
    {{/if}}

    var options = {{$graph->options|@json}};
    var ph = jQuery("#{{$graph->getId()}}");
    {{if $exam_audio->_can->edit}}
      ph.bind("plotclick", function (event, pos, item) {
        if (item) {
          ExamAudio.changeTympanValue('{{$graph->side}}', Math.round(pos.x), null,  {{if $old_consultation_id}}{{$old_exam_audio->_id}}{{/if}});
        }
        else {
          ExamAudio.changeTympanValue('{{$graph->side}}', Math.round(pos.x), Math.round(pos.y),  {{if $old_consultation_id}}{{$old_exam_audio->_id}}{{/if}});
        }
      });
    {{/if}}
    ph.bind("plothover", function (event, pos, item) {
      jQuery("#flot-tooltip").remove();

      if (item) {
        var title = this.retrieve("plot").getOptions().title;
        
        var content = "Modifier l'admittance #{value} ml pour #{label} à la pression #{pression} mm H²0".interpolate({
          value: item.datapoint[1],
          label: title,
          pression: 100 * item.datapoint[0] - 400
        });

        $$("body")[0].insert(DOM.div({className: "tooltip", id: "flot-tooltip"}, content).setStyle({
          top: pos.pageY + 5 + "px",
          left: pos.pageX + 5 + "px"
        }));
      }
    });
    
    var plot = jQuery.plot(ph, data, options);
    
    ph[0].store("plot", plot);
    
    ph[0].insert(DOM.div({
      className: 'axisLabel xaxisLabel', style: 'font-size: 10px;'
    }, options.xaxis.label));

    ph[0].insert(DOM.div({
      className: 'axisLabel yaxisLabel', style: 'font-size: 10px; text-indent: -50px;'
    }, options.yaxis.label));
  });
</script>

<strong>{{$graph->options.title}}</strong>
<br/>
<div id="{{$graph->getId()}}" style="width: 280px; height: 160px; display: inline-block;"></div>
