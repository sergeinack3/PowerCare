{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  filterActors = function(filter) {
    var form = filter.form
    var text = filter.value.toLowerCase()
    var select = form.elements.actor_guid
    Array.from(select.options).forEach(function (option) {
      if (!text) {
        option.style.display = 'block';
        return;
      }

      if (!option.text.toLowerCase().includes(text)) {
        option.style.display = 'none';
      }
    });

  }
</script>
{{if count($domain->_ref_group_domains) == 0}}
  <div class="small-warning">{{tr}}CDomain-msg-no group domain{{/tr}}</div>
  {{mb_return}}
{{/if}}
{{if $domain->incrementer_id}}
  <div class="small-warning">{{tr}}CDomain-actor_none{{/tr}}</div>
  {{mb_return}}
{{/if}}

{{if $domain->actor_id}}
  {{assign var=actor value=$domain->_ref_actor}}

  {{mb_include template=inc_list_actor actor=$domain->_ref_actor}}
{{else}}
  <div class="small-info">{{tr}}CDomain-actor_id-desc{{/tr}}</div>
  <table class="form">
    <tr>
      <td>
        <form name="editActor" action="?m={{$m}}" method="post" onsubmit="return onSubmitFormAjax(this, { onComplete : function() {
          Domain.refreshListIncrementerActor('{{$domain->_id}}'); Domain.refreshListDomains(); }});">

          <input type="hidden" name="m" value="eai" />
          <input type="hidden" name="dosql" value="do_domain_actor_aed" />
          <input type="hidden" name="domain_id" value="{{$domain->_id}}" />
          <input type="hidden" name="del" value="0" />

          <div class="me-margin-bottom-5">
            <input type="text" placeholder="Filtres" onchange="filterActors(this)"/>
          </div>

          <div>
            <select name="actor_guid" onchange="this.form.onsubmit()">
              <option value="">&mdash;</option>
              {{foreach from=$actors key=_actor_class item=_actors}}
                <option value="" disabled> &mdash; {{tr}}{{$_actor_class}}{{/tr}} &mdash; </option>
                {{foreach from=$_actors key=_sub_actor_class item=_sub_actors}}
                  <optgroup label="{{tr}}{{$_sub_actor_class}}{{/tr}}">
                    {{foreach from=$_sub_actors item=_actor}}
                      <option value="{{$_actor->_guid}}">{{$_actor}}</option>
                    {{/foreach}}
                  </optgroup>
                {{/foreach}}
              {{/foreach}}
            </select>
          </div>
        </form>
      </td>
    </tr>
  </table>
{{/if}}
