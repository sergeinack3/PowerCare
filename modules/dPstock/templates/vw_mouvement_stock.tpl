{{*
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=commentaire_dispensation value=""}}

<td>
  {{if $_mvt_stock->source_class}}
    <span onmouseover="ObjectTooltip.createEx(this, '{{$_mvt_stock->source_class}}-{{$_mvt_stock->source_id}}')">
      {{tr}}{{$_mvt_stock->source_class}}{{/tr}}
      {{if $_mvt_stock->source_class == "CStockSejour"}}
        - {{$_mvt_stock->_ref_source->_ref_sejour->_ref_patient->_view}}
      {{/if}}
    </span>
  {{else}}
    -
  {{/if}}
</td>
<td>
  {{if $_mvt_stock->cible_class}}
    <span onmouseover="ObjectTooltip.createEx(this, '{{$_mvt_stock->cible_class}}-{{$_mvt_stock->cible_id}}')">
            {{tr}}{{$_mvt_stock->cible_class}}{{/tr}}
      {{if $_mvt_stock->cible_class == "CStockSejour"}}
        - {{$_mvt_stock->_ref_cible->_ref_sejour->_ref_patient->_view}}
      {{/if}}
    </span>
  {{else}}
    -
  {{/if}}
</td>
<td>
  {{if "dispensation general use_dispentation_ucd"|gconf}}
    {{'Ox\Mediboard\Medicament\CMedicamentArticle::getLibelleForUCD'|static_call:$_mvt_stock->_ref_produit}}
  {{else}}
    {{$_mvt_stock->_ref_produit->_view}}
  {{/if}}
</td>
<td>{{mb_value object=$_mvt_stock field=type}}</td>
<td>{{mb_value object=$_mvt_stock field=quantite}} {{$_mvt_stock->_ref_produit->_unite_dispensation}}</td>
<td>{{mb_value object=$_mvt_stock field=datetime}}</td>
<td>{{mb_value object=$_mvt_stock field=commentaire}}</td>
<td>{{$commentaire_dispensation}}</td>
<td>
  <span onmouseover="ObjectTooltip.createEx(this, '{{$_mvt_stock->_guid}}')">
  {{mb_value object=$_mvt_stock field=etat}}
  </span>
</td>
<td class="narrow button">
  {{if $_mvt_stock->etat == "en_cours"}}
    <input type="checkbox" name="_{{$_mvt_stock->_guid}}" class="mvt_stock"
           onchange="Mouvement.json['{{$_mvt_stock->_id}}'].checked = (this.checked ? 1 : 0);Mouvement.checkCountElts();" />
    <script>
      Mouvement.json["{{$_mvt_stock->_id}}"] = {checked: 0};
    </script>
  {{/if}}
</td>
