{{*
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function() {
    var form = getForm('unlock-{{$user->_id}}');

    {{if $auto_submit}}
      form.onsubmit();
    {{/if}}
  });
</script>

<form name="unlock-{{$user->_id}}" method="post" onsubmit="return onSubmitFormAjax(this, function(){location.reload();})">
  <input type="hidden" name="m" value="admin" />
  <input type="hidden" name="dosql" value="do_unlock_user" />

  <input type="hidden" name="user_id" value="{{$user->_id}}" />

  <input type="hidden" name="@token" value="{{$token}}" />

  <button type="submit" class="tick compact" {{if !$can->admin}}disabled{{/if}}>
    {{tr}}Unlock{{/tr}}
  </button>
</form>
