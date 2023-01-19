{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div class="big-info">
  <h2>Export d'infrastructure CSV</h2>

  <strong>L'export est effectu� pour l'�tablissement courant{{if $only_actifs}} <span style="color:red;">(uniquement les �l�ments actifs)</span>{{/if}}.</strong>
  <br />
  <br />

  Les champs export�s sont les suivants :
  <ol>
    <li><strong>service</strong> : Nom du service</li>
    <li><strong>chambre</strong> : Nom de la chambre</li>
    <li><strong>lit</strong> : Nom du lit</li>
    <li><strong>lit_complet</strong> : Nom complet du lit</li>
    <li><strong>ufh_service</strong> : Codes des unit�s fonctionnelles d'h�bergement li�es au service (s�par�es par |)</li>
    <li><strong>ufh_chambre</strong> : Codes des unit�s fonctionnelles d'h�bergement li�es � la chambre (s�par�es par |)</li>
    <li><strong>ufh_lit</strong> : Codes des unit�s fonctionnelles d'h�bergement li�es au lit (s�par�es par |)</li>
    <li><strong>ufs_service</strong> : Codes des unit�s fonctionnelles de soins li�es au service (s�par�es par |)</li>
    <li><strong>ufs_chambre</strong> : Codes des unit�s fonctionnelles de soins li�es � la chambre (s�par�es par |)</li>
    <li><strong>ufs_lit</strong> : Codes des unit�s fonctionnelles de soins li�es au lit (s�par�es par |)</li>
    <li><strong>prestas</strong> : Noms des prestations du lit (s�par�s par |)</li>
    {{if !$only_actifs}}
      <li><strong>service_actif</strong> : {{tr}}CService.export actif{{/tr}}</li>
      <li><strong>chambre_actif</strong> : {{tr}}CChambre.export actif{{/tr}}</li>
      <li><strong>lit_actif</strong> : {{tr}}CLit.export actif{{/tr}}</li>
    {{/if}}
  </ol>
  <br />
</div>

<table class="main form">
  <tr>
    <td class="button">
      <a class="button fas fa-external-link-alt" target="_blank" href="?m=dPhospi&raw=ajax_export_infra_csv&only_actifs={{$only_actifs}}">{{tr}}Export{{/tr}}</a>
    </td>
  </tr>
</table>
