{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<!-- Patient -->
<th>{{tr}}CPatient-Last name / First name{{/tr}}</th>
<th>{{tr}}CPatient-Birth (Age){{/tr}}</th>
{{if $filter->_coordonnees}}
  <th>{{tr}}CPatient-adresse{{/tr}}</th>
  <th>{{tr}}CPatient-tel-court{{/tr}}</th>
{{/if}}
<th>{{tr}}CPatient-rques{{/tr}}</th>