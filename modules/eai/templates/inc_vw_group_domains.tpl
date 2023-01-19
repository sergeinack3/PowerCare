{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $domain->_id}}
  <button type="button" class="add" onclick="Domain.editGroupDomain(null, '{{$domain->_id}}')">{{tr}}Add{{/tr}}</button>
{{/if}}

{{if count($domain->_ref_group_domains) == 0}}
  <div class="small-warning">{{tr}}CDomain-group_domain-none{{/tr}}</div>
  {{mb_return}}
{{/if}}
<table class="tbl main">
  <tr>
    <th class="category narrow"></th>
    <th class="category">{{mb_label class="CGroupDomain" field="group_id"}}</th>
    <th class="category">{{mb_label class="CGroupDomain" field="object_class"}}</th>
    <th class="category">{{mb_label class="CGroupDomain" field="master"}}</th>
  </tr>

  {{foreach from=$domain->_ref_group_domains item=_group_domain}}
    {{mb_include template=inc_list_group_domains group_domain=$_group_domain}}
  {{/foreach}}
</table>