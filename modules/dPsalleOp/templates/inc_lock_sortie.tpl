{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function() {
    getForm("lock_sortie").user_password.focus();
  });
</script>

<form name="lock_sortie" method="post" action="?m=system&a=ajax_password_action"
      onsubmit="return onSubmitFormAjax(this, {useFormAction: true})">
  <input type="hidden" name="callback" value="callbackSortie" />
  <table class="form">
    <tr>
      <th>
        Anesthésiste
      </th>
      <td>
        <select name="user_id">
          {{assign var=selected value=""}}
          {{if $app->_ref_user->isAnesth()}}
            {{assign var=selected value=$app->user_id}}
          {{/if}}
          {{mb_include module=mediusers template=inc_options_mediuser list=$anesths selected=$selected}}
        </select>
      </td>
    </tr>
    <tr>
      <th>
        <label for="user_password">Mot de passe</label>
      </th>
      <td>
        <input type="password" name="user_password" class="notNull password str" />
      </td>
    </tr>
    <tr>
      <td class="button" colspan="2">
        <button type="button" class="tick singleclick" onclick="return this.form.onsubmit();">Valider</button>
      </td>
    </tr>
</form>