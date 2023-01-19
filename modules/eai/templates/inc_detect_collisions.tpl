{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th rowspan="2"></th>
    <th class="category" colspan="2">Séjour HL7</th>
    <th class="category" colspan="2">Séjour MB</th>
    <th rowspan="2" class="category">Action</th>
  </tr>
  <tr>
    <th class="category">Entrée</th>
    <th class="category">Sortie</th>
    <th class="category">Entrée</th>
    <th class="category">Sortie</th>
  </tr>
  
  {{foreach from=$collisions item=_collision}}
    <tbody class="hoverable">
      <tr>
        <th>Prévue</th>
        <td>{{mb_value object=$_collision.hl7 field=entree_prevue}}</td>
        <td>{{mb_value object=$_collision.hl7 field=sortie_prevue}}</td>
        <td>{{mb_value object=$_collision.mb field=entree_prevue}}</td>
        <td>{{mb_value object=$_collision.mb field=sortie_prevue}}</td>
        <td rowspan="2">
          <a class="button search notext" href="index.php?m=dPplanningOp&tab=vw_edit_sejour&sejour_id={{$_collision.mb->_id}}" target="_blank">
            Visualiser le séjour dans Mediboard
          </a>
        </td>
      </tr>
      <tr>
        <th>Réélle</th>
        <td>{{mb_value object=$_collision.hl7 field=entree_reelle}}</td>
        <td>{{mb_value object=$_collision.hl7 field=sortie_reelle}}</td>
        <td>{{mb_value object=$_collision.mb field=entree_reelle}}</td>
        <td>{{mb_value object=$_collision.mb field=sortie_reelle}}</td>
      </tr>
    </tbody>
  {{/foreach}}
</table>
