{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_include module=system template=inc_pagination change_page=changePage}}

<table class="tbl">
  <tr>
    <th>{{tr}}Sejour{{/tr}}</th>
    <th>{{tr}}CActiviteCsARR-code{{/tr}}</th>
    <th>{{tr}}CActeCsARR-administration_id{{/tr}}</th>
    <th>{{tr}}CActeCsARR-modulateurs{{/tr}}</th>
    <th>{{tr}}CActeCsARR-phases{{/tr}}</th>
  </tr>

  {{foreach from=$evenements item=_evenement}}
  {{assign var=sejour value=$_evenement->_ref_sejour}}
  {{assign var=rowspan value=1}}
  {{if $dry_run}}
    {{assign var=rowspan value=$_evenement->_ref_actes_csarr|@count}}
  {{/if}}
  <tr>
    <td rowspan="{{$rowspan}}">
      {{mb_include module=system template=inc_vw_mbobject object=$sejour}}
    </td>
    {{if $dry_run}}
      {{foreach from=$_evenement->_ref_actes_csarr item=_acte}}
          <td>{{$_acte->code}}</td>
          <td>{{$_acte->administration_id}}</td>
          <td>{{$_acte->modulateurs}}</td>
          <td>{{$_acte->phases}}</td>
        </tr>
        <tr>
      {{/foreach}}
    {{else}}
      <td colspan="4">
        {{$_evenement->_actes_deleted}} {{tr}}CEvenementSSR-_actes_deleted{{/tr}}
      </td>
      </tr>
    {{/if}}
  {{foreachelse}}
    <tr>
      <td colspan="5" class="empty">{{tr}}CEvenementSSR-_no_doublon{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>