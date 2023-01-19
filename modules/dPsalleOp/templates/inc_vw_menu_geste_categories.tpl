{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{mb_default var=chapitre_selected value=false}}
{{mb_default var=element_selected  value=""}}

{{assign var=counter_categories value=0}}

{{if $categories}}
  {{assign var=counter_categories value=$categories|@count}}
{{/if}}

<script>
  Main.add(function () {
    {{if $chapitre_selected}}
      $('counter_categorie').innerHTML = '{{$counter_categories}}';
    {{/if}}
  });
</script>

<ul id="categories_geste">
  {{if $chapitre_selected}}
    {{foreach from=$categories item=_categorie}}
      {{assign var=counter_gestes value=0}}

      {{if $_categorie->_ref_gestes_perop}}
        {{assign var=counter_gestes value=$_categorie->_ref_gestes_perop|@count}}
      {{/if}}
      <li id="category_{{$_categorie->_id}}">
        <div class="categories-container {{if !$_categorie->_id}}{{$element_selected}}{{/if}}"
             {{if $counter_gestes || $_categorie->libelle == "Aucune catégorie"}}onclick="GestePerop.showMenuGestes(this, '{{$_categorie->_id}}', null, 1, '{{$see_all_gestes}}');"{{/if}}>
          {{if $counter_gestes || $_categorie->libelle == "Aucune catégorie"}}
            <span class="fold">
              <i class="far fa-caret-square-right fa-lg"></i>
            </span>
          {{/if}}
          <span title="{{$_categorie->description|smarty:nodefaults}}">
              {{$_categorie->libelle}}
            </span>
        </div>
      </li>
    {{foreachelse}}
      <li>
        <div class="categories-container" onclick="">
        <span class="empty">
          {{tr}}CAnesthPeropCategorie.none{{/tr}}
        </span>
      </li>
    {{/foreach}}
  {{else}}
    <li>
      <div class="categories-container" onclick="">
        <span class="empty">
          {{tr}}CAnesthPeropChapitre-No chapter of selected{{/tr}}
        </span>
      </div>
    </li>
  {{/if}}
</ul>
