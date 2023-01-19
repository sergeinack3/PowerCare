{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div class="big-info">
  <h2>Export d'Unit�s fonctionnelles CSV</h2>

  <strong>L'export est effectu� pour l'�tablissement courant.</strong>
  <br />
  <br />

  Les champs export�s sont les suivants :
  <ol>
    <li><strong>prestation</strong> : Nom de la prestation</li>
    <li><strong>type</strong> : Type de prestation (ponctuelle ou journaliere)</li>
    <li><strong>type_admission</strong> : Type d'admissions ({{$types_admission}})</li>
    <li><strong>M</strong> : Prise en charge de la m�decine (0 ou 1)</li>
    <li><strong>C</strong> : Prise en charge de la chirurgie (0 ou 1)</li>
    <li><strong>O</strong> : Prise en charge de l'obst�trique (0 ou 1)</li>
    <li><strong>SSR</strong> : Prise en charge du SSR (0 ou 1)</li>
    <li><strong>item</strong> : Item de la prestation</li>
    <li><strong>rang</strong> : Rang de l'item</li>
    <li><strong>identifiant_externe</strong> : Identifiants externes li�s � l'item. Pour chaque identifiant on a id_externe|�tiquette
      et chaque identifiant est s�par� par ||
    </li>
  </ol>
  <br />
</div>

<table class="main form">
  <tr>
    <td class="button">
      <a class="button fas fa-external-link-alt" target="_blank"
         href="?m=dPhospi&raw=ajax_export_prestation_csv">{{tr}}Export{{/tr}}</a>
    </td>
  </tr>
</table>
