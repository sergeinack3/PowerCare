{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $subject->_ref_actes_lpp }}
  <table class="tbl fixed">
    <tr>
      <th class="title" colspan="3">{{tr}}CActeLPP|pl{{/tr}}</th>
    </tr>
    <tr>
      <th>{{mb_title class="CActeLPP" field=code}}</th>
      <th>{{mb_title class="CActeLPP" field=execution}}</th>
      <th>{{mb_title class="CActeLPP" field=_montant_facture}}</th>
    </tr>
      {{foreach from=$subject->_ref_actes_lpp item=acte_lpp}}
        <tr>
          <td class="text width50">
            <span onmouseover="ObjectTooltip.createEx(this, '{{$acte_lpp->_guid}}');">{{$acte_lpp->code}}</span>
          </td>
          <td>
              {{mb_value object=$acte_lpp field=execution}}
          </td>
          <td>
              {{mb_value object=$acte_lpp field=_montant_facture}}
          </td>
        </tr>
      {{/foreach}}
  </table>
{{/if}}
