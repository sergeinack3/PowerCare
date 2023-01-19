{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<tr>
  <th colspan="5" class="category">{{tr}}module-dPplanningOp-court{{/tr}}</th>
</tr>

{{mb_include template=inc_pref spec=bool var=create_dhe_with_read_rights}}
{{mb_include template=inc_pref spec=enum values="config|0|1" var=protocole_mandatory}}