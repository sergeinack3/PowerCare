{{*
 * @package Mediboard\Labo
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="form">

  <tr>
    <th>{{mb_label object=$examen field="conservation"}}</th>
    <td colspan="7">{{mb_field object=$examen field="conservation"}}</td>
  </tr>

  <tr>
    <th>{{mb_label object=$examen field="temps_conservation"}}</th>
    <td colspan="7">{{mb_field object=$examen field="temps_conservation"}} jours</td>
  </tr>

  <tr>
    <th>{{mb_label object=$examen field="quantite_prelevement"}}</th>
    <td colspan="7">{{mb_field object=$examen field="quantite_prelevement"}}</td>
  </tr>

  <tr>
    <th>{{mb_label object=$examen field="unite_prelevement"}}</th>
    <td colspan="7">{{mb_field object=$examen field="unite_prelevement"}}</td>
  </tr>

  <tr>
    <th rowspan="2">Jours d'exécution</th>
    <th class="text">{{mb_label object=$examen field="execution_lun"}}</th>
    <th class="text">{{mb_label object=$examen field="execution_mar"}}</th>
    <th class="text">{{mb_label object=$examen field="execution_mer"}}</th>
    <th class="text">{{mb_label object=$examen field="execution_jeu"}}</th>
    <th class="text">{{mb_label object=$examen field="execution_ven"}}</th>
    <th class="text">{{mb_label object=$examen field="execution_sam"}}</th>
    <th class="text">{{mb_label object=$examen field="execution_dim"}}</th>
  </tr>

  <tr>
    <td>{{mb_field object=$examen field="execution_lun" separator="<br/>"}}</td>
    <td>{{mb_field object=$examen field="execution_mar" separator="<br/>"}}</td>
    <td>{{mb_field object=$examen field="execution_mer" separator="<br/>"}}</td>
    <td>{{mb_field object=$examen field="execution_jeu" separator="<br/>"}}</td>
    <td>{{mb_field object=$examen field="execution_ven" separator="<br/>"}}</td>
    <td>{{mb_field object=$examen field="execution_sam" separator="<br/>"}}</td>
    <td>{{mb_field object=$examen field="execution_dim" separator="<br/>"}}</td>
  </tr>

  <tr>
    <th>{{mb_label object=$examen field="duree_execution"}}</th>
    <td colspan="7">{{mb_field object=$examen field="duree_execution"}} heures</td>
  </tr>

  <tr>
    <th>{{mb_label object=$examen field="remarques"}}</th>
    <td colspan="7">{{mb_field object=$examen field="remarques"}}</td>
  </tr>

</table>
