{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $subject->_ref_actes_ngap }}
<table class="tbl">
  <tr>
    <th class="title" colspan="3">{{tr}}CActeNGAP|pl{{/tr}}</th>
  </tr>
  <tr>
    <th>{{mb_title class="CActeNGAP" field=code}}</th>
    <th>{{mb_title class="CActeNGAP" field=execution}}</th>
    <th>{{mb_title class="CActeNGAP" field=_montant_facture}}</th>
  </tr>
  {{foreach from=$subject->_ref_actes_ngap item=acte_ngap}}
  <tr>
    <td class="text width50">
      <span onmouseover="ObjectTooltip.createEx(this, '{{$acte_ngap->_guid}}');">{{$acte_ngap->_shortview}}:</span>
        {{$acte_ngap->_libelle}}
    </td>
    <td>
      {{mb_value object=$acte_ngap field=execution}}
    </td>
    <td>
      {{mb_value object=$acte_ngap field=_montant_facture}}
    </td>
  </tr>
  {{/foreach}}
</table>
{{/if}}
