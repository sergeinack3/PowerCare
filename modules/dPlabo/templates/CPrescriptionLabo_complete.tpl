{{*
 * @package Mediboard\Labo
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th class="title">Dr {{$object->_ref_praticien->_view}}</th>
    <th class="title">{{$object->_ref_patient->_view}}</th>
  </tr>
</table>

{{assign var="prescription" value=$object}}
{{mb_include module=labo template=inc_vw_examens_prescriptions}}