{{*
 * @package Mediboard\System
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

<form name="editFrm" action="?m={{$m}}" method="post" onsubmit="return checkForm(this)">
  <input type="hidden" name="@class" value="{{$object->_class}}" />
  <input type="hidden" name="del" value="0" />
  {{mb_key object=$object}}
  
  <table class="tbl tooltip">
    <tr>
      <td class="button">
        {{if $object->_can->edit}}
        <button class="trash" type="button" onclick="SourceToViewSender.confirmDeletion(this.form)">
          {{tr}}Delete{{/tr}}
        </button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>