{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var="float"}}

{{if $patient->_overweight}}
  <img src="images/pictures/overweight.png"
       title="Patient{{if $patient->sexe == "f"}}e{{/if}} en surpoids ({{$patient->_overweight}} kg)"
       style="{{if $float}}float: {{$float}};{{/if}}" />
{{/if}}