{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$count_collision && !$sejours_futur}}
  {{mb_return}}
{{/if}}

{{if $count_collision}}
  <div class="small-warning">
    {{tr}}CSejour-Collisions detected{{/tr}}
  </div>
{{/if}}

<table class="tbl">
  <tr>
    <th class="title" colspan="5">{{if $sejour_collision}}Séjour en collision{{else}}Choix du séjour{{/if}}</th>
  </tr>
  <tr>
    <th>{{tr}}CSejour{{/tr}}</th>
    <th>{{mb_title class=CSejour field=type}}</th>
    <th>{{mb_title class=CSejour field=_motif_complet}}</th>
    <th>{{mb_title class=CSejour field=praticien_id}}</th>
    <th>Erreur fusion</th>
  </tr>
  {{if $count_collision}}
    <tr>
      <td>
        <label>
          <input type="radio" name="sejour_id_merge" value="{{$sejour_collision->_id}}" disabled checked />
          <span onmouseover="ObjectTooltip.createEx(this, '{{$sejour_collision->_guid}}')">
                  {{$sejour_collision->_view}}
          </span>
        </label>
      </td>
      <td class="narrow">{{tr}}CSejour.type.{{$sejour_collision->type}}{{/tr}}</td>
      <td class="text compact">{{$sejour_collision->_motif_complet}}</td>
      <td>{{mb_include module=mediusers template=inc_vw_mediuser mediuser=$sejour_collision->_ref_praticien}}</td>
      <td>{{mb_include module="dPurgences" template="inc_result_check_merge" message=$check_merge}}</td>
    </tr>
  {{/if}}
  {{if $sejours_futur}}
    {{if !$count_collision}}
      <tr class="selected">
        <td class="narrow" colspan="5">
          <label>
            <input type="radio" name="sejour_id_merge"
                   onclick="this.up('tr').addUniqueClassName('selected'); SuiviGrossesse.toggleFieldsHospitalize(this);"
                   value="" checked
                   data-praticien_id="{{$sejour->praticien_id}}"
                   data-praticien_id_view="{{$sejour->_ref_praticien->_view}}"
                   data-uf_soins_id="{{$sejour->uf_soins_id}}"
                   data-mode_entree="{{$sejour->mode_entree}}"
                   data-mode_entree_id="{{$sejour->mode_entree_id}}"
                   data-ATNC="{{$sejour->ATNC}}">
             Continuer l'hospitalisation sans fusionner avec un séjour existant
          </label>
        </td>
      </tr>
    {{/if}}
    {{foreach from=$sejours_futur item=_sejour_futur}}
      <tr>
        <td class="narrow">
          <label>
            <input type="radio" name="sejour_id_merge" value="{{$_sejour_futur->_id}}"
                   onclick="this.up('tr').addUniqueClassName('selected'); SuiviGrossesse.toggleFieldsHospitalize(this);"
                   data-praticien_id="{{$_sejour_futur->praticien_id}}"
                   data-praticien_id_view="{{$_sejour_futur->_ref_praticien->_view}}"
                   data-uf_soins_id="{{$_sejour_futur->uf_soins_id}}"
                   data-mode_entree="{{$_sejour_futur->mode_entree}}"
                   data-mode_entree_id="{{$_sejour_futur->mode_entree_id}}"
                   data-atnc="{{$_sejour_futur->ATNC}}" />
            <span onmouseover="ObjectTooltip.createEx(this, '{{$_sejour_futur->_guid}}')">
              {{$_sejour_futur->_view}}
            </span>
          </label>
        </td>
        <td class="narrow">{{tr}}CSejour.type.{{$_sejour_futur->type}}{{/tr}}</td>
        <td class="text compact">{{$_sejour_futur->_motif_complet}}</td>
        <td>{{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_sejour_futur->_ref_praticien}}</td>
        <td id="result_merge_{{$_sejour_futur->_id}}"></td>
      </tr>
    {{/foreach}}
  {{/if}}
</table>