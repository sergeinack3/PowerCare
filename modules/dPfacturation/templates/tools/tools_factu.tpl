{{*
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=facturation script=tools ajax=1}}
{{mb_script module=cabinet script=edit_consultation  ajax=1}}
{{mb_script module=patients script=evenement_patient ajax=1}}

<table class="tbl">
  <tr>
    <th class="title" colspan="2">{{tr}}Facturation-tools{{/tr}}</th>
  </tr>
  <tr>
    <td>
      <button class="fas fa-exclamation-circle" onclick="FactuTools.showElements()">
        {{tr}}Facturation-tools-view-error-bills{{/tr}}
      </button>
    </td>
    <td>{{tr}}Facturation-tools-view-error-bills-desc{{/tr}}</td>
  </tr>
  <tr>
    <td>
      <button class="fas fa-exclamation-circle" onclick="FactuTools.showMultiFactures()">
        {{tr}}Facturation-tools-view-error-multifactures{{/tr}}
      </button>
    </td>
    <td>{{tr}}Facturation-tools-view-error-multifactures-desc{{/tr}}</td>
  </tr>
  <tr>
    <td>
      <button class="fas fa-exclamation-circle" onclick="FactuTools.showPaidFactures()">
        {{tr}}Facturation-tools-view-error-paid-factures{{/tr}}
      </button>
    </td>
    <td>{{tr}}Facturation-tools-view-error-paid-factures-desc{{/tr}}</td>
  </tr>
  <tr>
    <td>
      <button class="fas fa-exclamation-circle" onclick="FactuTools.seeFactEtab(1, 0)">
        {{tr}}CFacture-tools-seeFactEtab{{/tr}}
      </button>
    </td>
    <td>{{tr}}CFacture-tools-seeFactEtab-desc{{/tr}}</td>
  </tr>
</table>
