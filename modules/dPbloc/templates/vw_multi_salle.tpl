{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=bloc script=multiSalle ajax=$ajax}}

<script>
  Main.add(function() {
    MultiSalle.salles_ids = {{$salles_ids|@json}};
    MultiSalle.chir_id    = '{{$chir_id}}';
    MultiSalle.date       = '{{$date}}';
    MultiSalle.reloadOpsPlanning();
  });
</script>

<table class="main">
  <tr>
    <td id="list_ops" style="width: 33%;"></td>
    <td id="planning_ops"></td>
  </tr>
</table>