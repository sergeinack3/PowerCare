{{*
 * @package Mediboard\Repas
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $type && $validation->validationrepas_id && $validation->modif}}
  <form action="?m={{$m}}" method="post">
    <input type="hidden" name="m" value="{{$m}}" />
    <input type="hidden" name="del" value="0" />
    <input type="hidden" name="dosql" value="do_validationrepas_aed" />
    <input type="hidden" name="date" value="{{$date}}" />
    <input type="hidden" name="service_id" value="{{$service_id}}" />
    <input type="hidden" name="typerepas_id" value="{{$type}}" />
    <input type="hidden" name="validationrepas_id" value="{{$validation->validationrepas_id}}" />
    <input type="hidden" name="modif" value="0" />
    <button type="submit" class="tick">Valider les modifications</button>
  </form>
{{/if}}
<table class="tbl">
  {{if $service_id}}
    {{foreach from=$service->_ref_chambres item=curr_chambre}}
      {{foreach from=$curr_chambre->_ref_lits item=curr_lit}}
        {{foreach from=$curr_lit->_ref_affectations item=curr_affect}}
          {{assign var="repas" value=$curr_affect->_list_repas.$date.$type}}
          <tr id="affect{{$curr_affect->_id}}-trigger">
            <th class="category">
              <div style="float:right">
                {{if $repas->repas_id && !$repas->menu_id}}
                  [PAS DE REPAS]
                {{elseif $repas->_is_modif}}
                  <em>{{$repas->_ref_menu->nom}}</em>
                {{else}}
                  {{$repas->_ref_menu->nom}}
                {{/if}}
                {{if $repas->modif}}
                  {{me_img src="warning.png" icon="warning" class="me-warning" alt="modifié"}}
                {{/if}}
              </div>
              Chambre {{$curr_chambre->_view}} - {{$curr_lit->_view}}
            </th>
          </tr>
          {{if $repas->_ref_menu->menu_id}}
            <tbody class="effectChambre" id="affect{{$curr_affect->_id}}" style="display:none;">
            <tr>
              <td>
                {{foreach from=$plat->_specs.type->_list item=curr_typePlat}}
                  {{if $repas->$curr_typePlat}}
                    {{assign var="ref" value=_ref_$curr_typePlat}}
                    <em>{{$repas->$ref->nom}}</em>
                    <br />
                  {{elseif $repas->_ref_menu->$curr_typePlat}}
                    {{$repas->_ref_menu->$curr_typePlat}}
                    <br />
                  {{/if}}
                {{/foreach}}
              </td>
            </tr>
            </tbody>
          {{/if}}
        {{/foreach}}
      {{/foreach}}
      {{foreachelse}}
      <tr>
        <th class="category">Pas de repas prévu dans ce service</th>
      </tr>
    {{/foreach}}
  {{else}}
    <tr>
      <th class="category">Veuillez sélectionner un service</th>
    </tr>
  {{/if}}
</table>

<script type="text/javascript">
  PairEffect.initGroup("effectChambre", {sEffect: "appear"});
</script>