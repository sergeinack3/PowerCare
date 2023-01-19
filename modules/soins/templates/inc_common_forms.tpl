{{*
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="addConsultation" method="post" action="?">
  <input type="hidden" name="m" value="cabinet" />
  <input type="hidden" name="dosql" value="do_consult_now" />
  <input type="hidden" name="_prat_id" value="{{$app->_ref_user->_id}}" />
  <input type="hidden" name="patient_id" value="{{$sejour->patient_id}}" />
  <input type="hidden" name="sejour_id" value="{{$sejour->_id}}" />
  <input type="hidden" name="_operation_id" value="" />
  <input type="hidden" name="type" value="" />
  <input type="hidden" name="_in_suivi" value="1" />
  <input type="hidden" name="callback" value="Soins.modalConsult.curry({{$sejour->_id}})" />
</form>