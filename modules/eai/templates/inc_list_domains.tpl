{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="listGroupDomains" action="?m={{$m}}" method="post">
  <table class="tbl me-striped me-table-col-separated">
    <tr>
      <th>
        <button type="button" class="merge notext compact" title="{{tr}}Merge{{/tr}}" style="float: left;" onclick="Domain.resolveConflicts(this.form);">
          {{tr}}Merge{{/tr}}
        </button>
      </th>
      <th>{{mb_title object=$domain field=tag}}</th>
      <th>{{mb_title object=$domain field=libelle}}</th>
      <th>{{mb_title object=$domain field=OID}}</th>
      <th>{{mb_title object=$domain field=incrementer_id}}</th>
      <th>{{mb_title object=$domain field=actor_id}}</th>
      <th>{{tr}}CDomain-back-group_domains{{/tr}}</th>
      <th>{{mb_title object=$domain field=_is_master_ipp}}</th>
      <th>{{mb_title object=$domain field=_is_master_nda}}</th>
      <th>{{mb_title object=$domain field=_count_objects}}</th>
    </tr>
    {{foreach from=$domains item=_domain}}
      <tr class="{{if $_domain->_id == $domain->_id}}selected{{/if}} {{if !$_domain->active}}opacity-30{{/if}}">
        <td class="narrow">
          <input type="checkbox" name="domains_id[]" value="{{$_domain->_id}}" class="merge" style="float: left;" onclick="checkOnlyTwoSelected(this)" />
        </td>
        <td><button type="button" class="edit notext" onclick="Domain.showDomain('{{$_domain->_id}}', this)">{{tr}}Edit{{/tr}}</button>
            {{mb_value object=$_domain field=tag}}
        </td>
        <td>
          <a href="#{{$_domain->_guid}}" onclick="Domain.showDomain('{{$_domain->_id}}', this)">
            {{mb_value object=$_domain field=libelle}}
          </a>
        </td>
        <td>
          <a href="#{{$_domain->_guid}}" onclick="Domain.showDomain('{{$_domain->_id}}', this)">
            {{mb_value object=$_domain field=OID}}
          </a>
        </td>
        {{assign var=incrementer value=$_domain->_ref_incrementer}}
        <td>
          <span onmouseover="ObjectTooltip.createEx(this, '{{$incrementer->_guid}}');">
            {{mb_value object=$incrementer field=_view}}
          </span>
        </td>
        {{assign var=actor value=$_domain->_ref_actor}}
        <td>
          <span onmouseover="ObjectTooltip.createEx(this, '{{$actor->_guid}}');">
            {{mb_value object=$actor field=_view}}
          </span>
        </td>
        <td>
          {{if $_domain->_ref_group_domains > 0}}
            <ul>
              {{foreach from=$_domain->_ref_group_domains item=_group_domain}}
                <li>{{$_group_domain->_ref_group->_view}}</li>
              {{/foreach}}
            </ul> 
          {{/if}}
        </td>
        <td {{if $_domain->_is_master_ipp}}class="ok"{{/if}}></td>
        <td {{if $_domain->_is_master_nda}}class="ok"{{/if}}></td>
        <td>
          <button type="button" class="lookup notext" onclick="Domain.showDetails('{{$_domain->_id}}')">{{tr}}Details{{/tr}}</button>
          <div id="domain_details-{{$_domain->_id}}"></div>
        </td>
      </tr>
    {{foreachelse}}
      <tr>
        <td colspan="4" class="empty">{{tr}}CDomain.none{{/tr}}</td>
      </tr>
    {{/foreach}}
  </table>
</form>