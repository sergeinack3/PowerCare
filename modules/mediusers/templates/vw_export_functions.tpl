{{*
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div class="big-info">
  <h2>Export de fonctions</h2>

  <strong>L'export est effectué pour l'établissement courant.</strong>
  <br/>
  <br/>

  Les champs exportés sont les suivants :
  <ol>
    <li><strong>intitule</strong> : {{tr}}CFunctions-text{{/tr}}</li>
    <li><strong>sous-titre</strong> : {{tr}}CFunctions-soustitre{{/tr}}</li>
    <li><strong>type</strong> : {{tr}}CFunctions-type{{/tr}}</li>
    <li><strong>couleur</strong> : {{tr}}CFunctions-color{{/tr}}</li>
    <li><strong>initales</strong> : {{tr}}CFunctions-initials{{/tr}}</li>
    <li><strong>adresse</strong> : {{tr}}CFunctions-adresse{{/tr}}</li>
    <li><strong>cp</strong> : {{tr}}CFunctions-cp{{/tr}}</li>
    <li><strong>ville</strong> : {{tr}}CFunctions-ville{{/tr}}</li>
    <li><strong>tel</strong> : {{tr}}CFunctions-tel{{/tr}}</li>
    <li><strong>fax</strong> : {{tr}}CFunctions-fax{{/tr}}</li>
    <li><strong>mail</strong> : {{tr}}CFunctions-email{{/tr}}</li>
    <li><strong>siret</strong> : {{tr}}CFunctions-siret{{/tr}}</li>
    <li><strong>quotas</strong> : {{tr}}CFunctions-quotas{{/tr}}</li>
    <li><strong>actif</strong> : {{tr}}CFunctions-actif{{/tr}}</li>
    <li><strong>compta_partage</strong> : {{tr}}CFunctions-compta_partagee{{/tr}}</li>
    <li><strong>consult_partage</strong> : {{tr}}CFunctions-consults_events_partagees{{/tr}}</li>
    <li><strong>adm_auto</strong> : {{tr}}CFunctions-admission_auto{{/tr}}</li>
    <li><strong>facturable</strong> : {{tr}}CFunctions-facturable{{/tr}}</li>
    <li><strong>creation_sejours</strong> : {{tr}}CFunctions-create_sejour_consult{{/tr}}</li>
    <li><strong>ufs</strong> : Codes des unités fonctionnelles médicales séparés par |</li>
    <li><strong>ufs_secondaires</strong> : Codes des unités fonctionnelles médicales secondaires séparés par |</li>
  </ol>
  <br/>
</div>

<table class="main form">
  <tr>
    <td class="button">
      <a class="button fas fa-external-link-alt" target="_blank" href="?m=mediusers&raw=ajax_export_functions">{{tr}}Export{{/tr}}</a>
    </td>
  </tr>
</table>
