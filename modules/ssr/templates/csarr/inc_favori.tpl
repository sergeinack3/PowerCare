{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=callback value=false}}
{{mb_default var=uid value=''}}
{{mb_default var=float value=true}}

<form name="editFavoriCsARR-{{$code->code}}{{$uid}}" action="?" method="post" onsubmit="">
  <input type="hidden" name="@class" value="CFavoriCsARR">
  <input type="hidden" name="favori_csarr_id" value="{{$code->_favori_id}}">
  <input type="hidden" name="code" value="{{$code->code}}">
  <input type="hidden" name="user_id" value="{{$user->_id}}">
  <input type="hidden" name="del" value="0">

  <span id="editFavoriCsARR-{{$code->code}}{{$uid}}-del" class="csarr-favori" style="{{if !$code->_favori_id}}display: none;{{/if}}{{if $float}}float: right;{{/if}}">
    <i class="fas fa-star" style="height: 16px; color: goldenrod;" title="{{tr}}CActiviteCsARR-msg-is_favori{{/tr}}"></i>
    <span class="button" title="{{tr}}CActiviteCsARR-action-delete_from_favori{{/tr}}" onclick="CsARR.deleteCodeFromFavorite('{{$code->code}}', '{{$uid}}');">
      <i class="fas fa-minus-circle"></i>
    </span>
  </span>

  <span id="editFavoriCsARR-{{$code->code}}{{$uid}}-add" class="csarr-favori" style="{{if $code->_favori_id}}display: none;{{/if}}{{if $float}}float: right;{{/if}}">
    <i class="far fa-star" style="color: goldenrod;" title="{{tr}}CActiviteCsARR-msg-is_not_favori{{/tr}}"></i>
    <span class="button" title="{{tr}}CActiviteCsARR-action-add_to_favori{{/tr}}" onclick="CsARR.addCodeToFavorite('{{$code->code}}', '{{$uid}}');">
      <i class="fas fa-plus-circle"></i>
    </span>
  </span>
</form>