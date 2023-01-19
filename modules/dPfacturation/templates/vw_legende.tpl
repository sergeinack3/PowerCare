{{*
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th colspan="2">{{tr}}CFacture{{/tr}}</th>
  </tr>
  {{foreach from=$statutes item=status}}
    {{if $status!=="non_cloture" || !"dPfacturation $classe use_auto_cloture"|gconf}}
      <tr>
        <td class="me-w40px facturestatus-color-{{$status}}"></td>
        <td class="text">{{tr}}CFacture-_statut.{{$status}}{{/tr}}</td>
      </tr>
    {{/if}}
  {{/foreach}}
</table>
