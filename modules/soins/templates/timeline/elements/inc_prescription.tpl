{{*
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $type == "administer"}}
  <table class="main layout">
    <tr>
      <td>
          <span class="type_item circled">
            {{tr}}CPrescriptionLineElement-event-administration{{/tr}}
          </span>
      </td>
    </tr>

    {{foreach from=$list item=item name=dispenser}}
      <tr>
        <td style="width: 50%;">
          <span onmouseover="ObjectTooltip.createEx(this, '{{$item->_guid}}');">
            {{$item}}
          </span>
          <br/>
          {{mb_value object=$item field=dateTime}}
          <br/>
          {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$item->_ref_administrateur}}
        </td>
        <td style="width: 75%">
          <span class="timeline_description">
            {{assign var=line value=$item->_ref_object}}
            {{if $line->_class == 'CPrescriptionLineMedicament' || $line->_class == 'CPrescriptionLineMixItem'}}
              {{if $item->_view_unite_prise}}
                {{'Ox\Mediboard\Medicament\CMedicamentProduit::replaceUnitKG'|static_call:$item->_view_unite_prise}}
              {{else}}
                {{if "planSoins general unite_prescription_plan_soins"|gconf}}
                  {{$item->quantite}}
                  {{$item->_libelle_unite_prescription}}
                {{else}}
                  {{$item->quantite}}
                  {{$item->_ref_object->_unite_reference_libelle}}
                {{/if}}
                ({{$line->_ucd_view}})
              {{/if}}
            {{elseif $line->_class == 'CPrescriptionLineElement'}}
              {{$item->quantite}}
              {{if $line->_ref_element_prescription->_ref_category_prescription->chapitre}}
                {{tr}}CCategoryPrescription.chapitre.{{$line->_ref_element_prescription->_ref_category_prescription->chapitre}}{{/tr}}
              {{/if}}
              ({{$line->_ref_element_prescription->_view}})
            {{/if}}
          </span>
        </td>
      </tr>
      {{if !$smarty.foreach.dispenser.last}}
        <tr>
          <td colspan="2">
            <hr class="item_separator"/>
          </td>
        </tr>
      {{/if}}
    {{/foreach}}
  </table>
{{/if}}

{{if $type == "prescription_begin"}}
  <table class="main layout">
    <tr>
      <td>
        <span class="type_item circled">
          {{if is_array($list) && count($list) > 1}}
            {{tr var1=$list|@count}}mod-soins-Beggining of %s amount of prescription lines{{/tr}}
          {{else}}
            {{tr}}mod-soins-Beggining of one prescription line{{/tr}}
          {{/if}}
        </span>
      </td>
    </tr>
    <tr>
      <td style="width: 50%;">
        <br>
        <span onmouseover="ObjectTooltip.createEx(this, '{{$list[0]->_guid}}');">
          {{mb_value object=$list[0] field=_debut_reel}}
        </span>
        <br/>
        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$list[0]->_ref_praticien}}
      </td>
      <td>
        <span class="timeline_description">
        {{foreach from=$list item=item name=objects_loop}}
          {{if is_array($list) && count($list) > 1}}
            <span onmouseover="ObjectTooltip.createEx(this, '{{$list[0]->_guid}}');">
          {{/if}}

          {{if $item->_class == 'CPrescriptionLineMedicament'}}
            {{$item->_ucd_view}}
            {{if !$item->_ref_produit->isMV() && $item->_dci_view && $item->generiquable && 'mpm general show_dci_prescription'|gconf}}({{$item->_dci_view}}){{/if}}
            {{if 'Ox\Mediboard\Medicament\CMedicamentProduit::isProduitRisque'|static_call:$item->_ref_produit}}
              <span style="color: red">({{tr}}CProduitLivretTherapeutique-risque-court{{/tr}}) </span>
            {{/if}}
            {{if 'Ox\Mediboard\Medicament\CMedicamentProduit::isProduitATBControle'|static_call:$item->_ref_produit}}
            <span style="color: purple">({{tr}}CProduitLivretTherapeutique-atb_controle-court{{/tr}})</span>
          {{/if}}
          {{elseif $item->_class == 'CPrescriptionLineMix'}}
            {{foreach from=$item->_ref_lines item=_item name=items}}
            {{$_item->_ucd_view}}
            {{if !$smarty.foreach.items.last}}
              <br/>
            {{/if}}
          {{/foreach}}
          {{elseif $item->_class == 'CPrescriptionLineElement'}}
            {{$item->_ref_element_prescription->_view}}
          {{/if}}

          {{if is_array($list) && count($list) > 1}}
            </span>
          {{/if}}

          {{if !$smarty.foreach.objects_loop.last}}
            <tr>
              <td colspan="2">
                <hr class="item_separator"/>
              </td>
            </tr>
          {{/if}}
        {{/foreach}}
      </span>
      </td>
    </tr>
  </table>
{{/if}}

{{if $type == "prescription_end"}}
  <table class="table layout">
    <tr>
      <td>
        <span class="type_item circled">
          {{if is_array($list) && count($list) > 1}}
            {{tr var1=$list|@count}}mod-soins-Beggining of %s amount of prescription lines{{/tr}}
          {{else}}
            {{tr}}mod-soins-End of one prescription line{{/tr}}
          {{/if}}
        </span>
      </td>
    </tr>
    <tr>
      <td style="width: 50%;">
        <span onmouseover="ObjectTooltip.createEx(this, '{{$list[0]->_guid}}');">
          {{mb_value object=$list[0] field=_fin_reelle}}
        </span>
        <br>
        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$list[0]->_ref_praticien}}
      </td>
      <td>
        <span class="timeline_description">
          {{foreach from=$list item=item name=objects_loop}}
            {{if is_array($list) && count($list) > 1}}
              <span onmouseover="ObjectTooltip.createEx(this, '{{$list[0]->_guid}}');">
            {{/if}}

            {{if $item->_class == 'CPrescriptionLineMedicament'}}
              {{$item->_ucd_view}}
              {{if !$item->_ref_produit->isMV() && $item->_dci_view && $item->generiquable && 'mpm general show_dci_prescription'|gconf}}({{$item->_dci_view}}){{/if}}
              {{if 'Ox\Mediboard\Medicament\CMedicamentProduit::isProduitRisque'|static_call:$item->_ref_produit}}
                <span style="color: red">({{tr}}CProduitLivretTherapeutique-risque-court{{/tr}})</span>
              {{/if}}
              {{if 'Ox\Mediboard\Medicament\CMedicamentProduit::isProduitATBControle'|static_call:$item->_ref_produit}}
                <span style="color: purple">({{tr}}CProduitLivretTherapeutique-atb_controle-court{{/tr}})</span>
              {{/if}}
            {{elseif $item->_class == 'CPrescriptionLineMix'}}
              {{foreach from=$item->_ref_lines item=_item name=items}}
                {{$_item->_ucd_view}}
                {{if !$smarty.foreach.items.last}}
                  <br/>
                {{/if}}
              {{/foreach}}
            {{elseif $item->_class == 'CPrescriptionLineElement'}}
              {{$item->_ref_element_prescription->_view}}
            {{/if}}

          {{if is_array($list) && count($list) > 1}}
            </span>
          {{/if}}

          {{if !$smarty.foreach.objects_loop.last}}
            <tr>
              <td colspan="2">
                <hr class="item_separator"/>
              </td>
            </tr>
          {{/if}}
        {{/foreach}}
      </span>
      </td>
    </tr>
  </table>
{{/if}}
