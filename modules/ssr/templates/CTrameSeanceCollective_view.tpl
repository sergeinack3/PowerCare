{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_include module=system template=CMbObject_view}}

{{if $app->user_prefs.edit_planning_collectif}}
  <table class="tbl">
    <tr>
      <td class="button">
        <button type="button" class="edit" onclick="TrameCollective.editTrame('{{$object->_id}}')">
          {{tr}}CTrameSeanceCollective-title-modify{{/tr}}
        </button>
      </td>
    </tr>
  </table>
{{/if}}