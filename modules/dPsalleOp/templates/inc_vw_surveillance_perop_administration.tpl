{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=prescription script=prescription ajax=true}}

<script>
  window.DMI_operation_id = '{{$operation->_id}}';
  Main.add(Prescription.updatePerop.curry('{{$sejour->_id}}', '', '{{$line_guid}}', '{{$planif_id_selected}}', '{{$type}}', '{{$datetime}}', '{{$administration_guid}}'));
</script>

<div id="perop"></div>
