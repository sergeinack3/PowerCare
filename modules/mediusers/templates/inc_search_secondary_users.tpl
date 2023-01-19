{{*
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th class="title" colspan="6">
      Recherche d'utilisateurs
    </th>
  </tr>
  <tr>
    <th>
      {{mb_title class=CMediusers field=_user_username}}
    </th>
    <th>
      {{mb_title class=CMediusers field=_user_last_name}}
    </th>
    <th>
      {{mb_title class=CMediusers field=_user_first_name}}
    </th>
    <th>
      {{mb_title class=CMediusers field=function_id}}
    </th>
    <th>
      {{mb_title class=CMediusers field=adeli}}
    </th>
    <th></th>
  </tr>
  {{foreach from=$users item=_user}}
    <tr>
      <td>
        {{mb_value object=$_user field=_user_username}}
      </td>
      <td>
        {{mb_value object=$_user field=_user_last_name}}
      </td>
      <td>
        {{mb_value object=$_user field=_user_first_name}}
      </td>
      <td>
        {{$_user->_ref_function}}
      </td>
      <td>
        {{mb_value object=$_user field=adeli}}
      </td>
      <td class="narrow">
        {{if ($_user->isSecondary() && $_user->main_user_id != $main_user->_id) || $_user->_ref_secondary_users|@count}}
          <i class="fas fa-exclamation-triangle fa-lg" style="color: goldenrod;" title="Cet utilisateur est déjà associé à un autre utilisateur"></i>
        {{elseif $_user->isSecondary() && $_user->main_user_id == $main_user->_id}}
          <form name="unlinkUser-{{$_user->_id}}" method="post" action="?" onsubmit="return onSubmitFormAjax(this, filterSecondaryUsers.curry(getForm('searchSecondaryUsers')));">
            <input type="hidden" name="m" value="{{$m}}" />
            <input type="hidden" name="dosql" value="do_mediusers_aed" />
            <input type="hidden" name="user_id" value="{{$_user->_id}}" />
            <input type="hidden" name="_user_id" value="{{$_user->_user_id}}" />
            <input type="hidden" name="main_user_id" value="">

            <button type="button" class="cancel notext" onclick="this.form.onsubmit();">
              Dissocier l'utilisateur
            </button>
          </form>
        {{else}}
          <form name="linkUser-{{$_user->_id}}" method="post" action="?" onsubmit="return onSubmitFormAjax(this, filterSecondaryUsers.curry(getForm('searchSecondaryUsers')));">
            <input type="hidden" name="m" value="{{$m}}" />
            <input type="hidden" name="dosql" value="do_mediusers_aed" />
            <input type="hidden" name="user_id" value="{{$_user->_id}}" />
            <input type="hidden" name="_user_id" value="{{$_user->_user_id}}" />
            <input type="hidden" name="main_user_id" value="{{$main_user->_id}}">

            <button type="button" class="fa fa-link notext" onclick="this.form.onsubmit();">
              Associer l'utilisateur
            </button>
          </form>
        {{/if}}
      </td>
    </tr>
  {{foreachelse}}
    <tr>
      <td class="empty" colspan="6">
        {{tr}}CMediusers.none{{/tr}}
      </td>
    </tr>
  {{/foreach}}
</table>