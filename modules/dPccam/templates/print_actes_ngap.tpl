{{*
 * $Id$
 *  
 * @category Ccam
 * @package  Mediboard
 * @author   SARL OpenXtrem <dev@openxtrem.com>
 * @license  GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 * @version  $Revision$
 * @link     http://www.mediboard.org
*}}

<table class="table table-bordered">
  <thead class="thead-inverse">
    <tr>
      <th class="title" colspan="11">Actes NGAP</th>
    </tr>
  </thead>
  <tr>
    <th>
      {{mb_title class=CActeNGAP field=quantite}}
    </th>
    <th>
      {{mb_title class=CActeNGAP field=code}}
    </th>
    <th>
      {{mb_title class=CActeNGAP field=coefficient}}
    </th>
    <th>
      {{mb_title class=CActeNGAP field=demi}}
    </th>
    <th>
      {{mb_title class=CActeNGAP field=montant_base}}
    </th>
    <th>
      {{mb_title class=CActeNGAP field=montant_depassement}}
    </th>
    <th>
      {{mb_title class=CActeNGAP field=complement}}
    </th>
    <th>
      {{mb_title class=CActeNGAP field=qualif_depense}}
    </th>
    <th>
      {{mb_title class=CActeNGAP field=execution}}
    </th>
    <th>
      {{mb_title class=CActeNGAP field=executant_id}}
    </th>
  </tr>
  {{foreach from=$object->_ref_actes_ngap item=_acte}}
    <tr>
      <td>
        {{mb_value object=$_acte field=quantite}}
      </td>
      <td>
        {{mb_value object=$_acte field=code}}
      </td>
      <td>
        {{mb_value object=$_acte field=coefficient}}
      </td>
      <td>
        {{mb_value object=$_acte field=demi}}
      </td>
      <td>
        {{mb_value object=$_acte field=montant_base}}
      </td>
      <td>
        {{mb_value object=$_acte field=montant_depassement}}
      </td>
      <td>
        {{mb_value object=$_acte field=qualif_depense}}
      </td>
      <td>
        {{mb_value object=$_acte field=execution}}
      </td>
      <td>
        {{$_acte->_ref_executant}}
      </td>
    </tr>
  {{foreachelse}}
    <tr>
      <td class="empty" colspan="10">{{tr}}CActeNGAP.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>