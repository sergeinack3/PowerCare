{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=show_edit value=true}}

{{if !$object->_can->read}}
  <div class="small-info">
    {{tr}}{{$object->_class}}{{/tr}} : {{tr}}access-forbidden{{/tr}}
  </div>
  {{mb_return}}
{{/if}}

{{mb_include module=system template=CMbObject_view}}

{{assign var=naissance value=$object}}

{{mb_script module=maternite script=naissance ajax=1}}
{{if $show_edit}}
  <table class="form">
    <tr>
      <td class="button">
        <button class="edit" onclick="Naissance.edit('{{$naissance->_id}}')">
          {{tr}}Edit{{/tr}}
        </button>
      </td>
    </tr>
  </table>
{{/if}}
