{{*
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=callback value=false}}
{{mb_default var=uid value=''}}
{{mb_default var=float value=true}}

{{if !$code->_is_category}}
  <form name="editFavoriCIM-{{$code->code}}{{$uid}}" action="?" method="post" onsubmit="">
    <input type="hidden" name="@class" value="CFavoriCIM10">
    <input type="hidden" name="favoris_id" value="{{$code->_favoris_id}}">
    <input type="hidden" name="favoris_code" value="{{$code->code}}">
    <input type="hidden" name="favoris_user" value="{{$user->_id}}">
    <input type="hidden" name="del" value="0">

    <span id="editFavoriCIM-{{$code->code}}{{$uid}}-del" class="cim10-favori" style="{{if !$code->_favoris_id}}display: none;{{/if}}{{if $float}}float: right;{{/if}}">
      <i class="fas fa-star" style="height: 16px; color: goldenrod;" title="{{tr}}CCodeCIM10-msg-is_favori{{/tr}}"></i>
      <span class="button" title="{{tr}}CCodeCIM10-action-delete_from_favori{{/tr}}" onclick="CIM.deleteCodeFromFavorite('{{$code->code}}', '{{$uid}}');">
        <i class="fas fa-minus-circle"></i>
      </span>
    </span>

    <span id="editFavoriCIM-{{$code->code}}{{$uid}}-add" class="cim10-favori" style="{{if $code->_favoris_id}}display: none;{{/if}}{{if $float}}float: right;{{/if}}">
      <i class="far fa-star" style="color: goldenrod;" title="{{tr}}CCodeCIM10-msg-is_not_favori{{/tr}}"></i>
      <span class="button" title="{{tr}}CCodeCIM10-action-add_to_favori{{/tr}}" onclick="CIM.addCodeToFavorite('{{$code->code}}', '{{$uid}}');">
        <i class="fas fa-plus-circle"></i>
      </span>
    </span>
  </form>
{{/if}}