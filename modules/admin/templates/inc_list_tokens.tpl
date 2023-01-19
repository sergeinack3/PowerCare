{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Tokens = {
    page : function(start) {
      var form = getForm('search-token');
      $V(form.start, start);
      form.onsubmit();
    },
    cronify: function() {
      new Url('admin', 'cronify_tokens') .
        addParam('token_ids', $$('input.token-checkbox:checked').pluck('value').join('-')) .
        requestModal();
    }
  }
</script>

{{mb_include module=system template=inc_pagination total=$total step=$limit current=$start change_page=Tokens.page}}

<table class="main tbl">
  <tr>
    <th class="narrow" colspan="2">
      <button type="button" class="list singleclick notext" title="Produire des cron lines" onclick="Tokens.cronify()" />
    </th>
    <th class="narrow">{{mb_title class=CViewAccessToken field=hash}}</th>
    <th>{{mb_title class=CViewAccessToken field=user_id}}</th>
    <th>{{mb_title class=CViewAccessToken field=label}}</th>
    <th>{{mb_title class=CViewAccessToken field=module_action_id}}</th>
    <th>{{mb_title class=CViewAccessToken field=params}}</th>

    <th title="{{tr}}CViewAccessToken-restricted-desc{{/tr}}">
      <i class="fa fa-lock"></i>
    </th>

    <th title="{{tr}}CViewAccessToken-purgeable-desc{{/tr}}">
      <i class="far fa-trash-alt"></i>
    </th>

    <th>{{tr}}CViewAccessToken-datetimes{{/tr}}</th>
    <th>{{tr}}CViewAccessToken-all_uses{{/tr}}</th>
    <th>{{mb_title class=CViewAccessToken field=max_usages}}</th>
    <th>{{mb_title class=CViewAccessToken field=total_use}}</th>
    <th>{{mb_title class=CViewAccessToken field=_mean_usage_duration}}</th>
    <th>{{mb_title class=CViewAccessToken field=validator}}</th>
    <th class="narrow text">{{tr}}CViewAccessToken-back-jobs{{/tr}}</th>
  </tr>
  
  {{foreach from=$tokens item=_token}}
    <tr {{if !$_token->isValid()}} class="hatching" {{/if}}>
      <td>
        <input class="token-checkbox" name="token-checkbox" type="checkbox" value="{{$_token->_id}}"/>
      </td>

      {{mb_include module=admin template=inc_inline_token _token = $_token}}
    </tr>
  {{foreachelse}}
    <tr>
      <td class="empty" colspan="16">{{tr}}CViewAccessToken.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>
