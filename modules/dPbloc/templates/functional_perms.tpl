{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<tr>
  <th colspan="5" class="category">{{tr}}module-dPbloc-court{{/tr}}</th>
</tr>
{{mb_include template=inc_pref spec=bool var=allowed_check_entry_bloc}}
{{mb_include template=inc_pref spec=bool var=drag_and_drop_horizontal_planning}}
{{mb_include template=inc_pref spec=enum var=bloc_planning_visibility values='user_rights|function|restricted'}}