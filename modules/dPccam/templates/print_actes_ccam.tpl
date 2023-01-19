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
<table class="table table-bordered table-hover">
  <thead class="thead-inverse">
    <tr>
      <th class="title" colspan="11">Actes CCAM</th>
    </tr>
  </thead>
  <tr>
    <th>
      {{mb_title class=CActeCCAM field=code_activite}}
    </th>
    <th>
      {{mb_title class=CActeCCAM field=code_extension}}
    </th>
    <th>
      {{mb_title class=CActeCCAM field=executant_id}}
    </th>
    <th>
      {{mb_title class=CActeCCAM field=facturable}}
    </th>
    <th>
      {{mb_title class=CActeCCAM field=code_association}}
    </th>
    <th>
      {{mb_title class=CActeCCAM field=modificateurs}}
    </th>
    <th>
      {{mb_title class=CActeCCAM field=extension_documentaire}}
    </th>
    <th>
      {{mb_title class=CActeCCAM field=_tarif}}
    </th>
    <th>
      {{mb_title class=CActeCCAM field=execution}}
    </th>
    <th>
      {{mb_title class=CActeCCAM field=montant_depassement}}
    </th>
    <th>
      {{mb_title class=CActeCCAM field=motif_depassement}}
    </th>
  </tr>
  {{foreach from=$object->_ext_codes_ccam item=_code}}
    <tr>
      <th class="section" colspan="11">
        {{$_code->code}} : {{$_code->libelleLong}}
      </th>
    </tr>
    {{foreach from=$_code->activites item=_activite}}
      {{foreach from=$_activite->phases item=_phase}}
        {{assign var="acte" value=$_phase->_connected_acte}}
        {{if $acte->_id}}
          <tr>
            <td class="narrow">
              <span class="circled {{if $acte->_id}}ok{{else}}error{{/if}}">
                {{mb_value object=$acte field=code_activite}}-{{mb_value object=$acte field=code_phase}}
              </span>
            </td>
            <td>
              {{mb_value object=$acte field=code_extension}}
            </td>
            <td>
              {{$acte->_ref_executant}}
            </td>
            <td>
              {{mb_value object=$acte field=facturable}}
            </td>
            <td>
              {{mb_value object=$acte field=code_association}}
            </td>
            <td {{if !$acte->_modificateurs|@count}}class="empty"{{/if}}>
              {{foreach from=$acte->_modificateurs item=_mod}}
                <span class="circled ok">
                  {{$_mod}}
                </span>
                {{foreachelse}}
                Aucun modificateur
              {{/foreach}}
            </td>
            <td>
              {{mb_value object=$acte field=extension_documentaire}}
            </td>
            <td>
              {{mb_value object=$acte field=_tarif}}
            </td>
            <td>
              {{mb_value object=$acte field=execution}}
            </td>
            <td>
              {{mb_value object=$acte field=montant_depassement}}
            </td>
            <td>
              {{mb_value object=$acte field=motif_depassement}}
            </td>
          </tr>
        {{/if}}
      {{/foreach}}
    {{/foreach}}
    {{foreachelse}}
    <tr>
      <td class="empty" colspan="11">{{tr}}CActeCCAM.none{{/tr}}</td>
    </tr>
  {{/foreach}}
</table>
