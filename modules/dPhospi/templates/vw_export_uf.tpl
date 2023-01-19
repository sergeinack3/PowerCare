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
    <li><strong>code</strong> : Code de l'unit� fonctionnelle</li>
    <li><strong>libelle</strong> : Libell� de l'unit� fonctionnelle</li>
    <li><strong>type</strong> : Type de l'unit� fonctionnelle (hebergement, medicale ou soins)</li>
    <li><strong>type_sejour</strong> : Type de s�jour (comp, ambu, exte, seances, ssr, psy, urg ou consult</li>
  </ol>
  <br />
</div>

<table class="main form">
  <tr>
    <td class="button">
      <a class="button fas fa-external-link-alt" target="_blank" href="?m=dPhospi&raw=ajax_export_uf_csv">{{tr}}Export{{/tr}}</a>
    </td>
  </tr>
</table>
