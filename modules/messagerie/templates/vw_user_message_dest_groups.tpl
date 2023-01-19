{{*
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=messagerie script=UserMessageDestGroup}}

<script type="text/javascript">
  emptyUserFilter = function() {
    var form = getForm('filterDestGroups');
    $V(form.elements['user_filter'], '');
    $V(form.elements['user_filter_view'], '');
  };

  Main.add(function() {
    var form = getForm('filterDestGroups');
    UserMessageDestGroup.filters_form = form;
    new Url('mediusers', 'ajax_users_autocomplete')
      .addParam('input_field', 'user_filter_view')
      .autoComplete(form.elements['user_filter_view'], null, {
        minChars: 0,
        method: 'get',
        select: 'view',
        dropdown: true,
        afterUpdateElement: function(field, selected) {
          if ($V(form.elements['user_filter_view']) == "") {
            $V(form.elements['user_filter_view'], selected.down('.view').innerHTML);
          }

          $V(form.elements['user_filter'], selected.getAttribute("id").split("-")[2], true);
        }
      });
  });
</script>

<form name="filterDestGroups" method="post" action="?" onsubmit="return UserMessageDestGroup.filter();">
  <table class="form">
    <tr>
      <th class="title" colspan="4">
        <button type="button" class="new notext" style="float:left;"onclick="UserMessageDestGroup.edit();">{{tr}}New{{/tr}}</button>
        {{tr}}CUserMessageDestGroup-title-list{{/tr}}
      </th>
    </tr>
    <tr>
      <th>{{mb_label class=CUserMessageDestGroup field=name}}</th>
      <td>
        <input type="text" name="name_filter" value="">
      </td>
      <th>{{mb_label class=CUserMessageDestGroupUserLink field=user_id}}</th>
      <td>
        <input type="hidden" name="user_filter" value="">
        <input type="text" name="user_filter_view" value="">
        <button type="button" class="cancel notext" onclick="emptyUserFilter();">{{tr}}Empty{{/tr}}</button>
      </td>
    </tr>
    <tr>
      <td class="button" colspan="4">
        <button type="button" class="search" onclick="this.form.onsubmit();">{{tr}}Filter{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>

<div id="recipient_group_list">
  {{mb_include module=messagerie template=inc_list_user_message_dest_groups}}
</div>