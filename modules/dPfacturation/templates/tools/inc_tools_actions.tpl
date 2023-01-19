{{*
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $errors|@count}}
  <div class="small-error">
    {{foreach from=$errors item=_error}}
      {{$_error}}<br />
    {{/foreach}}
  </div>
{{/if}}

<table class="tbl">
  <tr>
    <th colspan="4" class="title">{{tr}}CFacture-tools-seeFactEtab{{/tr}}</th>
  </tr>
  <tr>
    <th>{{tr}}CFacture{{/tr}}</th>
    <th>{{tr}}CFactureCabinet-patient_id{{/tr}}</th>
    <th>{{tr}}CFactureCabinet-praticien_id{{/tr}}</th>
    <th>{{tr}}CFactureCabinet-group_id{{/tr}}</th>
  </tr>
  {{foreach from=$factures item=_facture}}
    <tr>
      <th>{{$_facture->_view}}</th>
      <th>{{mb_field object=$_facture field=patient_id}}</th>
      <th>{{mb_field object=$_facture field=praticien_id}}</th>
      <th>{{mb_field object=$_facture field=group_id}}</th>
    </tr>
  {{/foreach}}
  <tr>
    <td colspan="4">
      {{mb_include module=system template=inc_pagination total=$nb_factures step=$limit_see current=$page
          change_page=FactuTools.seeFactEtabPage}}
    </td>
  </tr>
</table>

{{if $factures|@count}}
  <button class="cleanup" type="button" onclick="FactuTools.seeFactEtab(0, 0);">{{tr}}CFacture-tools-actionFactEtab{{/tr}}</button>
{{/if}}
