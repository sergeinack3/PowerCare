{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
<tr>
    {{assign var=count value=0}}
    {{foreach from=$categories_rpu item=_categorie_rpu name=list_cats}}
    {{math equation=x+1 x=$count assign=count}}

    {{if $count == 6}}
</tr>
<tr>

    {{assign var=count value=0}}
    {{/if}}

    {{assign var=selected value=""}}
    {{foreach from=$rpu->_ref_rpu_categories item=_cat}}
        {{if $_cat->rpu_categorie_id == $_categorie_rpu->_id}}
            {{assign var=selected value=$_cat->_id}}
        {{/if}}
    {{/foreach}}

  <td>
    <div id="categorie_rpu_{{$_categorie_rpu->_id}}"
         class="categorie_rpu text"
         style="{{if $selected}}outline: 2px solid #000;{{/if}}"
         data-link_cat_id="{{$selected}}"
         onclick="Urgences.updateCategorie(this, '{{$_categorie_rpu->_id}}', '{{$rpu->sejour_id}}');">
        {{thumbnail document=$_categorie_rpu->_ref_icone profile=small style="width: 20px; height: 20px; background-color: transparent;"}}
      <div class="compact">{{$_categorie_rpu->motif}}</div>
    </div>
  </td>

    {{if $smarty.foreach.list_cats.last}}
        {{math equation=5-x x=$count assign=colspan}}

        {{if $colspan}}
          <td colspan="{{$colspan}}"></td>
        {{/if}}
    {{/if}}
    {{/foreach}}
</tr>
