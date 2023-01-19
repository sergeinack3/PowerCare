{{*
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="createSecondaryUser" method="post" action="?" onsubmit="return onSubmitFormAjax(this, filterSecondaryUsers.curry(getForm('searchSecondaryUsers')));">
  <input type="hidden" name="m" value="{{$m}}" />
  <input type="hidden" name="dosql" value="do_create_secondary_mediuser" />
  <input type="hidden" name="user_id" value="" />
  <input type="hidden" name="main_user_id" value="{{$main_user->_id}}">
  <table class="form">
    <tr>
      <th class="title" colspan="2">
        Création d'un compte secondaire
      </th>
    </tr>
    <tr>
      <th>
        {{mb_label object=$user field=adeli}}
      </th>
      <td>
        {{mb_field object=$user field=adeli}}
      </td>
    </tr>
    <tr>
      <td class="button" colspan="2">
        <button type="button" class="save" onclick="this.form.onsubmit();">{{tr}}Create{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>