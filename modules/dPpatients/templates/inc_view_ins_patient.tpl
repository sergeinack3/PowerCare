{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $patient->_count_ins}}
  <div style="float: right;" title="INS-C calculé pour le patient">
    <img src="images/icons/carte_vitale.png" onclick="Patient.openINS({{$patient->_id}})" />
  </div>
{{/if}}