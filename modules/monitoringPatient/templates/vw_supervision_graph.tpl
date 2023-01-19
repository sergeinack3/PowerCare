{{*
 * @package Mediboard\MonitoringPatient
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=monitoringPatient script=supervision_graph          ajax=1}}
{{mb_script module=monitoringPatient script=supervision_graph_defaults ajax=1}}
{{mb_script module=files             script=file                       ajax=1}}

<script>
  Main.add(SupervisionGraph.list.curry(SupervisionGraph.editGraph.curry({{$supervision_graph_id}})));
</script>

<table class="main layout">
  <tr>
    <td style="width: 400px;" id="supervision-list"></td>
    <td id="supervision-graph-editor" class="me-padding-left-4 me-padding-right-4">&nbsp;</td>
  </tr>
</table>