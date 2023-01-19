{{*
* @package Mediboard\Maternite
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

{{if $object->_can->edit}}
  {{mb_script module=maternite script=dossierMater ajax=1}}
  <table class="form">
    <tr>
      <td class="button">
        <form name="delete-{{$object->_guid}}" method="post" action="?" onsubmit="return onSubmitFormAjax(this.form);">
          {{mb_key object=$object}}
          {{mb_class object=$object}}
          <input type="hidden" name="del" value="1" />
          <button type="button" class="trash" onclick="
            return confirmDeletion(this.form, {
            typeName: $T('CAccouchement-of', '{{$object->date|date_format:$conf.date}}')}, Control.Modal.refresh);">
            {{tr}}Delete{{/tr}}
          </button>
        </form>
      </td>
    </tr>
  </table>
{{/if}}