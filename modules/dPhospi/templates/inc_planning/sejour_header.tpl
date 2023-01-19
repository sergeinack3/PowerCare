{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<!-- Sejour -->
{{if $filter->_by_date}}
  <th>{{mb_title class=CSejour field=praticien_id}}</th>
{{/if}}
<th>{{mb_title class=CSejour field=$filter->_horodatage}}</th>
<th>{{tr}}CSejour-_type_admission-court{{/tr}}</th>
<th>{{tr}}CSejour-_duree-court{{/tr}}</th>
<th>{{tr}}CProtocole-convalescence-court{{/tr}}</th>
<th>{{tr}}CChambre{{/tr}}</th>
{{if $prestation->_id}}
  <th>{{mb_value object=$prestation field=nom}}</th>
{{/if}}
<th>{{tr}}CSejour-rques-court{{/tr}}</th>
{{if $filter->_notes}}
  <th>{{tr}}common-Note|pl{{/tr}}</th>
{{/if}}
