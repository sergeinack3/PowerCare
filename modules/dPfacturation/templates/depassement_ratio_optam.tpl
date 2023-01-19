{{*
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="form">
  <tr>
    <th class="halfPane">Taux moyen de dépassement d'honoraires</th>
    <td class="halfPane">
      {{$ratio_montant_depassement}} %
      {{if $ratio_montant_depassement > $seuil_ratio_montant_depassement}}
        <i class="fas fa-exclamation-triangle fa-lg" style="color: goldenrod" title="{{tr}}Gestion-msg-ratio_montant_depassement{{/tr}}"></i>
      {{/if}}
    </td>
  </tr>
  <tr>
    <th class="halfPane">Taux d'activé réalisée au tarif opposable</th>
    <td class="halfPane">
      {{$ratio_consult_tarif_base}} %
      {{if $ratio_consult_tarif_base < $seuil_ratio_consult_tarif_base}}
        <i class="fas fa-exclamation-triangle fa-lg" style="color: goldenrod" title="{{tr}}Gestion-msg-ratio_consult_tarif_base{{/tr}}"></i>
      {{/if}}
    </td>
  </tr>
</table>