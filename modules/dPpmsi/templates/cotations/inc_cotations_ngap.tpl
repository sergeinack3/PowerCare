{{*
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $action === 'print'}}
  <script type="text/javascript">
    Main.add(function() {
      App.print();
    });
  </script>
{{/if}}

<table class="tbl">
  <tr>
    <th class="title" colspan="13">
        {{tr}}CActeNGAP|pl{{/tr}}
    </th>
  </tr>
  <tr>
    <th>
      {{mb_colonne class=CSejour field=patient_id order_col=$sort_column order_way=$sort_way function='sortCotationNGAP'}}
    </th>
    <th>
      NDA
    </th>
    <th>
      {{mb_title class=CActeNGAP field=object_id}}
    </th>
    <th>
      {{mb_colonne class=CActeNGAP field=executant_id order_col=$sort_column order_way=$sort_way function='sortCotationNGAP'}}
    </th>
    <th>
      {{mb_colonne class=CActeNGAP field=prescripteur_id order_col=$sort_column order_way=$sort_way function='sortCotationNGAP'}}
    </th>
    <th>
      {{mb_colonne class=CActeNGAP field=execution order_col=$sort_column order_way=$sort_way function='sortCotationNGAP'}}
    </th>
    <th>
      {{mb_colonne class=CActeNGAP field=code order_col=$sort_column order_way=$sort_way function='sortCotationNGAP'}}
    </th>
    <th>
      {{mb_colonne class=CActeNGAP field=quantite order_col=$sort_column order_way=$sort_way function='sortCotationNGAP'}}
    </th>
    <th>
      {{mb_colonne class=CActeNGAP field=coefficient order_col=$sort_column order_way=$sort_way function='sortCotationNGAP'}}
    </th>
    <th>
      {{mb_colonne class=CActeNGAP field=complement order_col=$sort_column order_way=$sort_way function='sortCotationNGAP'}}
    </th>
    <th>
      {{mb_colonne class=CActeNGAP field=montant_base order_col=$sort_column order_way=$sort_way function='sortCotationNGAP'}}
    </th>
    <th>
      {{mb_colonne class=CActeNGAP field=montant_depassement order_col=$sort_column order_way=$sort_way function='sortCotationNGAP'}}
    </th>
    <th>
      {{mb_colonne class=CActeNGAP field=_tarif order_col=$sort_column order_way=$sort_way function='sortCotationNGAP'}}
    </th>
  </tr>
  {{if $action != 'print'}}
    <tr>
      <td colspan="13">
        {{mb_include module=system template=inc_pagination total=$results.total current=$results.start change_page="changePageCotationsNGAP" step=$results.number}}
      </td>
    </tr>
  {{/if}}
  {{foreach from=$results.acts item=_act}}
    <tr>
      <td>
        <span onmouseover="ObjectTooltip.createEx(this, '{{$_act->_ref_object->_ref_patient->_guid}}'">
          {{$_act->_ref_object->_ref_patient}}
        </span>
      </td>
      <td>
        {{if $_act->object_class == 'CSejour'}}
          {{$_act->_ref_object->_NDA}}
        {{else}}
          {{$_act->_ref_object->_ref_sejour->_NDA}}
        {{/if}}
      </td>
      <td>
        <span onmouseover="ObjectTooltip.createEx(this, '{{$_act->_ref_object->_guid}}'">
          {{$_act->_ref_object}}
        </span>
      </td>
      <td>
        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_act->_ref_executant}}
      </td>
      <td>
        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_act->_ref_prescripteur}}
      </td>
      <td>
        {{mb_value object=$_act field=execution}}
      </td>
      <td>
        {{mb_value object=$_act field=code}}
      </td>
      <td>
        {{mb_value object=$_act field=quantite}}
      </td>
      <td>
        {{mb_value object=$_act field=coefficient}}
      </td>
      <td>
        {{mb_value object=$_act field=complement}}
      </td>
      <td style="text-align: right;">
        {{mb_value object=$_act field=montant_base}}
      </td>
      <td style="text-align: right;">
        {{mb_value object=$_act field=montant_depassement}}
      </td>
      <td style="text-align: right;">
        {{mb_value object=$_act field=_tarif}}
      </td>
    </tr>
  {{foreachelse}}
    <tr>
      <td class="empty" colspan="13">
        {{tr}}CActeNGAP.none{{/tr}}
      </td>
    </tr>
  {{/foreach}}
  {{if $action != 'print'}}
    <tr>
      <td colspan="13">
        {{mb_include module=system template=inc_pagination total=$results.total current=$results.start change_page="changePageCotationsNGAP" step=$results.number}}
      </td>
    </tr>
  {{/if}}
</table>