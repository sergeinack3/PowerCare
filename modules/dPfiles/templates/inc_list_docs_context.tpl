{{*
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="form">
  {{foreach from=$context_doc->_ref_documents item=_doc}}
  <tr>
    <td class="text">
      <button type="button" class="trash notext" style="float: right;"
              onclick="doDelDocContext('{{$_doc->_id}}');" title="{{tr}}Delete{{/tr}}">
      </button>
      {{$_doc}}
    </td>
  </tr>
  {{foreachelse}}
  <tr>
    <td class="empty" colspan="2">
      {{tr}}CCompteRendu.none{{/tr}}
    </td>
  </tr>
  {{/foreach}}
</table>