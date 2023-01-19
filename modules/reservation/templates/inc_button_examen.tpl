{{*
 * @package Mediboard\Reservation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=form value=""}}

<script>
  openExamens = function() {
    var form = getForm("{{$form}}");
    var url = new Url("reservation", "ajax_edit_examen");
    url.addParam("examen_operation_id", $V(form.examen_operation_id));
    url.requestModal(700, 550);
  }
  
  afterSaveExamen = function(examen_operation_id) {
    var form = getForm("{{$form}}");
    $V(form.examen_operation_id, examen_operation_id);
  }
</script>

<button type="button" class="new" onclick="openExamens()">Examens</button> 
