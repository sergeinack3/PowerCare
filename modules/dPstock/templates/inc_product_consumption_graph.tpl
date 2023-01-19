{{*
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{unique_id var=graph_id}}

<script type="text/javascript">
  Main.add(function () {
    var data = {{$data|@json}};

    data.options.mouse.trackFormatter = function (obj) {
      return parseInt(obj.y);
    }

    var container = $("consumption-graph-{{$graph_id}}");

    Flotr.draw(container, data.series, data.options);
  });
</script>

<div style="text-align: center;">
  <div style="width: 8px; height: 8px; display: inline-block; background-color: #66CC00;"></div>
  Entrées
  <div style="width: 8px; height: 8px; display: inline-block; background-color: #CB4B4B;"></div>
  Sorties
  <div style="width: 8px; height: 8px; display: inline-block; background-color: #6600CC;"></div>
  Périmés
</div>
<div style="width: {{$width}}px; height: {{$height}}px; margin: 0 auto;" id="consumption-graph-{{$graph_id}}"></div>
