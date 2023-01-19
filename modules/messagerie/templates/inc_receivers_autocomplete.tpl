{{*
 * @package Mediboard\Messagrie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<ul style="text-align: left;">
  {{foreach from=$users item=_user}}
    <li class="autocomplete" data-id="{{$_user->_id}}" data-guid="{{$_user->_guid}}" data-connected="{{$_user->_is_connected}}">
      <i class="fas fa-user {{if $_user->_is_connected}}connected-user{{else}}disconnected-user{{/if}}" title="{{tr}}CMediusers-msg-{{if $_user->_is_connected}}connected{{else}}disconnected{{/if}}{{/tr}}" style="float: right;"></i>
      {{mb_include module=mediusers template=CMediusers_autocomplete match=$_user show_view=true f=false}}
    </li>
  {{foreachelse}}
    <li>
      <span class="informal" style="font-style: italic;">
        {{tr}}No result{{/tr}}
      </span>
    </li>
  {{/foreach}}
</ul>