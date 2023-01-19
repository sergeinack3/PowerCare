{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
  var graph_day_data     = {{$graphs.day|@json}};
  var graph_hour_data    = {{$graphs.hour|@json}};
  var graph_ip_data      = {{$graphs.ip|@json}};
  
  Main.add(function(){
    Flotr.draw($('graph_day'), graph_day_data.series, graph_day_data.options);
    Flotr.draw($('graph_hour'), graph_hour_data.series, graph_hour_data.options);
    Flotr.draw($('graph_ip')  , graph_ip_data.series  , graph_ip_data.options);
  });
</script>

<form name="uploadFrm" action="?m=system&a=ajax_inspect_error_log" enctype="multipart/form-data" method="post">
  <input type="hidden" name="m" value="system" />
  <input type="hidden" name="a" value="ajax_inspect_error_log" />
  <input type="file" name="formfile[0]" size="0" />
  <button class="upload">Envoyer</button>
</form>

<br />

{{if $filename}}
  <h2>{{$filename}}</h2>
{{/if}}

<table class="main">
  <tr>
    <td>
      <div style="width: 600px; height: 480px; margin: 1em;" id="graph_day"></div>
    </td>
    <td>
      <div style="width: 600px; height: 480px; margin: 1em;" id="graph_hour"></div>
    </td>
  </tr>
  <tr>
    <td>
      <div style="width: 600px; height: 480px; margin: 1em;" id="graph_ip"></div>
    </td>
    <td>
    </td>
  </tr>
</table>