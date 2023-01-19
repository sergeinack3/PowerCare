{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=modal value=0}}

{{assign var=use_charge_price_indicator value="dPplanningOp CSejour use_charge_price_indicator"|gconf}}
{{assign var=box_id_mandatory value="dPurgences CRPU box_id_mandatory"|gconf}}

{{if $modal}}
  <button type="button" class="search" onclick="Modal.open('geoloc_div')">{{tr}}CRPU-Geolocalisation{{/tr}}</button>
{{/if}}

{{if $modal}}
<div id="geoloc_div" style="display: none;">
{{else}}
<fieldset class="me-small">
  <legend>{{tr}}CRPU-Geolocalisation{{/tr}}</legend>
{{/if}}

  <table class="form me-no-align me-no-box-shadow me-small-form">
    {{if $modal}}
      <tr>
        <th colspan="2" class="title">
          <button type="button" class="cancel notext" onclick="Control.Modal.close();" style="float: right;">{{tr}}Close{{/tr}}</button>
          {{tr}}CRPU-Geolocalisation{{/tr}}
        </th>
      </tr>
    {{/if}}

    {{assign var=required_uf_soins value="dPplanningOp CSejour required_uf_soins"|gconf}}
    {{if $required_uf_soins != "no"}}
      <tr>
        <th>
          {{mb_label object=$rpu field="_uf_soins_id"}}
        </th>
        <td>
          <select name="_uf_soins_id" class="ref {{if $required_uf_soins == "obl"}}notNull{{/if}}" style="width: 15em" onchange="{{$submit_ajax}}">
            <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
            {{foreach from=$ufs.soins item=_uf}}
              <option value="{{$_uf->_id}}" {{if $rpu->_uf_soins_id == $_uf->_id}}selected="selected"{{/if}}>
                {{mb_value object=$_uf field=libelle}}
              </option>
            {{/foreach}}
          </select>
        </td>
      </tr>
    {{/if}}
    {{assign var=config_charge_ATU value="dPurgences CRPU charge_ATU"|gconf}}
    {{if $use_charge_price_indicator != "no"}}
      <tr>
        <th>{{mb_label object=$rpu field="_charge_id"}}</th>
        <td>
          <select class="ref{{if $use_charge_price_indicator == "obl"}} notNull{{/if}}" name="_charge_id" style="width: 15em;" onchange="{{$submit_ajax}}">
            <option value=""> &ndash; {{tr}}Choose{{/tr}}</option>
            {{foreach from='Ox\Mediboard\PlanningOp\CChargePriceIndicator::getList'|static_call:null item=_cpi name=cpi}}
              {{if in_array($_cpi->type, 'Ox\Mediboard\PlanningOp\CSejour::getTypesSejoursUrgence'|static_call:$sejour->praticien_id) || ($rpu->_charge_id == $_cpi->_id)}}
                <option value="{{$_cpi->_id}}" {{if $rpu->_charge_id == $_cpi->_id || (!$rpu->_charge_id && $config_charge_ATU == $_cpi->_id)}}selected{{/if}}>
                  {{$_cpi|truncate:50:"...":false}}
                </option>
              {{/if}}
            {{/foreach}}
          </select>
        </td>
      </tr>
    {{/if}}

    <tr>
      {{assign var=box_id_not_null value=""}}
      {{if $box_id_mandatory && !$rpu->_id}}
        {{assign var=box_id_not_null value="notNull"}}
      {{/if}}
      <th style="width: 10em;">{{mb_label object=$rpu field="box_id" class=$box_id_mandatory}}</th>
      <td style="vertical-align: middle;" class="text">
        {{mb_include module=hospi template="inc_select_lit" field=box_id selected_id=$rpu->box_id
                     listService=$services reservations=$reservations_box classes=$box_id_not_null}}
        <button type="button" class="cancel opacity-60 notext" onclick="this.form.elements['box_id'].selectedIndex=0"></button>
        <div style="display: inline-block">
          &mdash; {{tr}}CRPU-_service_id{{/tr}} :
          {{if $services|@count == 1}}
            {{assign var=first_service value=$services_type|@first|@first}}
            <input type="hidden" name="_service_id" value="{{$first_service->_id}}" />
            {{$first_service->_view}}
          {{else}}
            <select name="_service_id" class="{{$sejour->_props.service_id}}" onchange="$V(this.form.box_id, '', false); {{$submit_ajax}}">
              <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
              {{foreach from=$services_type item=_services key=nom_serv}}
                <optgroup label="{{$nom_serv}}">
                  {{foreach from=$_services item=_service}}
                    <option value="{{$_service->_id}}"
                            {{if ($sejour->_id && ($sejour->_ref_curr_affectation->service_id == $_service->_id))
                             || (!$sejour->_id && ($sejour->service_id == $_service->_id))}}selected{{/if}}>
                      {{$_service->_view}}
                    </option>
                  {{/foreach}}
                </optgroup>
              {{/foreach}}
            </select>
          {{/if}}
        </div>
      </td>
    </tr>
  </table>

{{if $modal}}
</div>
{{else}}
</fieldset>
{{/if}}
