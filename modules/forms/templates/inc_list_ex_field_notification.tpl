{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function(){
    Control.Tabs.setTabCount('exclass-notifications', {{$notifications|@count}});
  });
</script>

{{if !$source_smtp || !$source_smtp->_id}}
  <div class="small-error">{{tr}}CExClassFieldNotification-msg-No SMTP source found{{/tr}}</div>
{{/if}}

{{if $notifications|@count > 3}}
  <div class="small-warning">
    {{tr}}CExClassFieldNotification-msg-A big number of notifications can bring to a slow down.{{/tr}}
  </div>
{{/if}}

<button class="new me-margin-top-4" onclick="ExFieldNotification.create(null, '{{$ex_class->_id}}')">
  {{tr}}CExClassFieldNotification-title-create{{/tr}}
</button>

<table class="main tbl me-no-align me-no-box-shadow">
  <tr>
    <th class="narrow"></th>
    <th>{{mb_title class=CExClassFieldNotification field=predicate_id}}</th>
    <th>{{mb_title class=CExClassFieldNotification field=target_user_id}}</th>
    <th>{{mb_title class=CExClassFieldNotification field=subject}}</th>
  </tr>

  {{foreach from=$notifications item=_notification}}
    <tr>
      <td>
        <button class="edit notext compact" onclick="ExFieldNotification.edit({{$_notification->_id}})">{{tr}}Edit{{/tr}}</button>
      </td>

      <td>{{mb_value object=$_notification field=predicate_id tooltip=true}}</td>
      <td>{{mb_value object=$_notification field=target_user_id tooltip=true}}</td>
      <td>{{mb_value object=$_notification field=subject}}</td>
    </tr>
    {{foreachelse}}
    <tr>
      <td colspan="4" class="empty">
        {{tr}}CExClassFieldNotification.none{{/tr}}
      </td>
    </tr>
  {{/foreach}}
</table>