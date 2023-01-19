{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$object->_can->read}}
  <div class="small-info">
    {{tr}}{{$object->_class}}{{/tr}} : {{tr}}access-forbidden{{/tr}}
  </div>
  {{mb_return}}
{{/if}}

{{mb_include module=system template=CMbObject_view}}

<table class="tbl tooltip">
  <tr>
    <td class="button">
      <button type="button" class="edit" onclick="GroupePatient.editGroupCategory('{{$object->_id}}');">
        {{tr}}CCategorieGroupePatient-action-Modify a group category{{/tr}}
      </button>
    </td>
  </tr>
</table>
