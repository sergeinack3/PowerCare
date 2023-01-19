{{*
 * @package Mediboard\Urgences
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
    <th class="title" colspan="10">
      {{tr}}CActe{{/tr}}
    </th>
  </tr>
  <tr>
    <th>
      {{mb_colonne class=CSejour field=patient_id order_col=$sort_column order_way=$sort_way function='sortBilanCotation'}}
    </th>
    <th>
      NDA
    </th>
    <th>
      {{mb_title class=CActeNGAP field=object_id}}
    </th>
    <th>
      {{mb_colonne class=CActeNGAP field=executant_id order_col=$sort_column order_way=$sort_way function='sortBilanCotation'}}
    </th>
    <th>
      {{mb_colonne class=CActeNGAP field=execution order_col=$sort_column order_way=$sort_way function='sortBilanCotation'}}
    </th>
    <th>
      {{mb_title class=CActeNGAP field=code}}
    </th>
    <th>
      <label title="{{tr}}CActe-majoration-desc{{/tr}}">{{tr}}CActe-majoration{{/tr}}</label>
    </th>
    <th>
      {{mb_title class=CActeNGAP field=montant_base}}
    </th>
    <th>
      {{mb_title class=CActeNGAP field=montant_depassement}}
    </th>
    <th>
      {{mb_title class=CActeNGAP field=_tarif}}
    </th>
  </tr>
  {{if $action !== 'print'}}
    <tr>
      <td colspan="10">
        {{mb_include module=system template=inc_pagination total=$results.total current=$results.start change_page='changePageBilanCotations' step=$results.number}}
      </td>
    </tr>
  {{/if}}
  <tbody id="BilanCotation-results-acts">
    {{foreach from=$results.acts item=_act}}
      <tr>
        <td style="text-align: center">
          <span onmouseover="ObjectTooltip.createEx(this, '{{$_act->_ref_patient->_guid}}'">
            {{mb_ditto name=patient_view value=$_act->_ref_patient->_view}}
          </span>
        </td>
        <td style="text-align: center">
          {{mb_ditto name=NDA value=$_act->_ref_sejour->_NDA}}
        </td>
        <td style="text-align: center">
          <span onmouseover="ObjectTooltip.createEx(this, '{{$_act->_ref_object->_guid}}'">
            {{mb_ditto name=codable_view value=$_act->_ref_object->_view}}
          </span>
        </td>
        <td style="text-align: center">
          {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_act->_ref_executant}}
        </td>
        <td style="text-align: center">
          {{mb_value object=$_act field=execution}}
        </td>
        {{if $_act->_class === 'CActeCCAM'}}
          <td style="text-align: center">
            {{mb_value object=$_act field=code_acte}}
            <span class="circled">
              {{mb_value object=$_act field=code_activite}}-{{mb_value object=$_act field=code_phase}}
            </span>
            {{mb_value object=$_act field=code_association}}
          </td>
          <td style="text-align: center">
            {{foreach from=$_act->_modificateurs item=_modificateur}}
              <span class="circled">{{$_modificateur}}</span>
            {{/foreach}}
          </td>
        {{else}}
          <td style="text-align: center">
            {{if $_act->quantite > 1}}
              <span title="{{tr}}CActeNGAP-quantite{{/tr}}">{{mb_value object=$_act field=quantite}} x </span>
            {{/if}}
            {{mb_value object=$_act field=code}}
            {{if $_act->coefficient != 1}}
              <span title="{{tr}}CActeNGAP-coefficient{{/tr}}"> {{mb_value object=$_act field=coefficient}}</span>
            {{/if}}
          </td>
          <td style="text-align: center">
            {{if $_act->complement}}
              <span class="circled" title="{{tr}}CActeNGAP.complement.{{$_act->complement}}{{/tr}}">
                {{mb_value object=$_act field=complement}}
              </span>
            {{/if}}
          </td>
        {{/if}}
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
        <td class="empty" colspan="10">
          {{tr}}CActe.none{{/tr}}
        </td>
      </tr>
    {{/foreach}}
  </tbody>
  {{if $action !== 'print'}}
    <tr>
      <td colspan="10">
        {{mb_include module=system template=inc_pagination total=$results.total current=$results.start change_page='changePageBilanCotations' step=$results.number}}
      </td>
    </tr>
  {{/if}}
</table>