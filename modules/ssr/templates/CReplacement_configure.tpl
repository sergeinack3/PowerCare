{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=class value=CReplacement}}
<form name="EditConfig-{{$class}}" method="post" onsubmit="return onSubmitFormAjax(this);">
  {{mb_configure module=$m}}

  <table class="form">
    <tr>
      <td class="button" colspan="2">
        <button class="modify">{{tr}}Save{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>

<button class="tick" onclick="new Url('ssr', 'ajax_check_replacements').requestModal(850, 500);" >
  {{tr}}mod-ssr-tab-ajax_check_replacements{{/tr}}
</button>
