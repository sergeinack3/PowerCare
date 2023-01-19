{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=required_uf_med         value="dPplanningOp CSejour required_uf_med"|gconf}}
{{assign var=required_uf_soins       value="dPplanningOp CSejour required_uf_soins"|gconf}}
{{assign var=show_field_circuit_ambu value="dPplanningOp CSejour show_circuit_ambu"|gconf}}

{{if $required_uf_soins === "no"}}
  {{mb_field object=$protocole field=uf_soins_id hidden=true}}
{{/if}}

{{if $required_uf_med === "no"}}
  {{mb_field object=$protocole field=uf_medicale_id hidden=true}}
{{/if}}

{{mb_field object=$protocole field=uf_hebergement_id hidden=true}}

<table class="form">
  <tr>
    <th class="category" colspan="2">Informations concernant le séjour</th>
  </tr>

  <tr>
    <th>{{mb_label object=$protocole field="libelle_sejour"}}</th>
    <td>{{mb_field object=$protocole field="libelle_sejour" style="width: 15em;"}}</td>
  </tr>

    {{if "eds"|module_active && "eds CSejour allow_eds_input"|gconf}}
        <tr>
            <th>
                {{mb_label object=$protocole field="code_EDS"}}
            </th>
            <td>
                {{mb_field object=$protocole field="code_EDS" emptyLabel="Choose"}}
            </td>
        </tr>
    {{/if}}

  <tr>
    <th>
      {{mb_label object=$protocole field="service_id"}}
    </th>
    <td>
      <select name="service_id" class="{{$protocole->_props.service_id}}" style="width: 15em;">
        <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
        {{foreach from=$listServices item=_service}}
          <option value="{{$_service->_id}}" {{if $protocole->service_id == $_service->_id}}selected{{/if}}>
            {{$_service->_view}}
          </option>
        {{/foreach}}
      </select>
    </td>
  </tr>

  {{if $required_uf_med !== "no"}}
    <tr>
      <th>{{mb_label object=$protocole field="uf_medicale_id"}}</th>
      <td>
        <select name="uf_medicale_id" class="ref">
          <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
          {{foreach from=$ufs.medicale item=_uf}}
            <option value="{{$_uf->_id}}" {{if $protocole->uf_medicale_id == $_uf->_id}}selected{{/if}}>
              {{mb_value object=$_uf field=libelle}}
            </option>
          {{/foreach}}
        </select>
      </td>
    </tr>
  {{/if}}

  {{if $required_uf_soins !== "no"}}
    <tr>
      <th>{{mb_label object=$protocole field="uf_soins_id"}}</th>
      <td>
        <select name="uf_soins_id" class="ref">
          <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
          {{foreach from=$ufs.soins item=_uf}}
            <option value="{{$_uf->_id}}" {{if $protocole->uf_soins_id == $_uf->_id}}selected{{/if}}>
              {{mb_value object=$_uf field=libelle}}
            </option>
          {{/foreach}}
        </select>
      </td>
    </tr>
  {{/if}}

  <tr>
    <th>
      {{mb_label object=$protocole field="DP"}}
    </th>
    <td>
      <script>
        Main.add(function() {
          CIM.autocomplete(getForm("editProtocole").keywords_code, null, {
            {{if 'dPcim10 diagnostics restrict_code_usage'|gconf}}
              field_type: 'dp',
              /* Permet de prendre en compte le type de séjour de façon dynamique */
              callback: function(input, queryString) {
                var form = getForm("editProtocole");
                var sejour_type = 'mco';
                if ($V(form.elements['type']) == 'ssr') {
                  sejour_type = 'ssr';
                }
                else if ($V(form.elements['type']) == 'psy') {
                  sejour_type = 'psy';
                }
                return queryString + "&sejour_type=" + sejour_type;
              },
            {{/if}}
            afterUpdateElement: function(input) {
              $V(getForm("editProtocole").DP, input.value);
            }
          });
        });
      </script>

      <input type="text" name="keywords_code" class="autocomplete str code cim10" value="{{$protocole->DP}}" style="width: 12em;" />
      <input type="hidden" name="DP" value="{{$protocole->DP}}" onchange="$V(this.form.keywords_code, this.value)"/>
      <button type="button" class="cancel notext" onclick="$V(this.form.DP, '');"></button>
      <button type="button" class="search notext" onclick="CIM.viewSearch($V.curry(this.form.elements['DP']), this.form.elements['chir_id']{{if 'dPcim10 diagnostics restrict_code_usage'|gconf}}, null, null, null, $V.curry(this.form.elements['type']), 'dp'{{/if}});">{{tr}}button-CCodeCIM10-choix{{/tr}}</button>
    </td>
  </tr>

  <tr>
    <th>
      {{mb_label object=$protocole field="DR"}}
    </th>
    <td>
      <script>
        Main.add(function() {
          CIM.autocomplete(getForm("editProtocole").DR_keywords_code, null, {
            {{if 'dPcim10 diagnostics restrict_code_usage'|gconf}}
              field_type: 'dr',
              /* Permet de prendre en compte le type de séjour de façon dynamique */
              callback: function(input, queryString) {
                var form = getForm("editProtocole");
                var sejour_type = 'mco';
                if ($V(form.elements['type']) == 'ssr') {
                  sejour_type = 'ssr';
                }
                else if ($V(form.elements['type']) == 'psy') {
                  sejour_type = 'psy';
                }
                return queryString + "&sejour_type=" + sejour_type;
              },
            {{/if}}
            afterUpdateElement: function(input) {
              $V(getForm("editProtocole").DR, input.value);
            }
          });
        });
      </script>

      <input type="text" name="DR_keywords_code" class="autocomplete str code cim10" value="{{$protocole->DR}}" style="width: 12em;" />
      <input type="hidden" name="DR" value="{{$protocole->DR}}" onchange="$V(this.form.DR_keywords_code, this.value)"/>
      <button type="button" class="cancel notext" onclick="$V(this.form.DR, '');"></button>
      <button type="button" class="search notext" onclick="CIM.viewSearch($V.curry(this.form.elements['DR']), this.form.elements['chir_id']{{if 'dPcim10 diagnostics restrict_code_usage'|gconf}}, null, null, null, $V.curry(this.form.elements['type']), 'dr'{{/if}});">{{tr}}button-CCodeCIM10-choix{{/tr}}</button>
    </td>
  </tr>

  <tr>
    <th>{{mb_label object=$protocole field="duree_hospi"}}</th>
    <td>{{mb_field object=$protocole field="duree_hospi" size="2" onchange="ProtocoleDHE.editHour();"}} {{tr}}night{{/tr}}(s)</td>
  </tr>

  <tr id="duree_heure_hospi_view">
    <th>{{mb_label object=$protocole field="duree_heure_hospi"}}</th>
    <td>{{mb_field object=$protocole field="duree_heure_hospi" size="2"}} {{tr}}hour{{/tr}}(s)</td>
  </tr>

  <tr>
    <th>{{mb_label object=$protocole field="time_entree_prevue"}}</th>
    <td colspan="3">
      <script>
        Main.add(function () {
          var options = {
            exactMinutes: false,
            minInterval: 15
          };
          var form = getForm('editProtocole');
          Calendar.regField(form.time_entree_prevue, null, options);
        });
      </script>
      {{mb_field object=$protocole field="time_entree_prevue"}}
    </td>
  </tr>

  {{if $use_charge_price_indicator != "no"}}
    <tr>
      <th>{{mb_label object=$protocole field="type"}}</th>
      <td>
        {{mb_field object=$protocole field="type" style="width: 15em;"
          onchange="ProtocoleDHE.onChangeType(this, true);"}}
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$protocole field="charge_id"}}</th>
      <td>
        <select class="ref" name="charge_id">
          <option value="">&ndash; {{tr}}Choose{{/tr}}</option>
          {{foreach from=$cpi_list item=_cpi name=cpi}}
            <option value="{{$_cpi->_id}}"
              {{if $protocole->charge_id == $_cpi->_id}}
                selected
              {{/if}}
                    data-type="{{$_cpi->type}}" data-type_pec="{{$_cpi->type_pec}}" data-hospit_de_jour="{{$_cpi->hospit_de_jour}}">
              {{$_cpi|truncate:50:"...":false}}
            </option>
          {{/foreach}}
        </select>
      </td>
    </tr>
  {{else}}
    <tr>
      <th>{{mb_label object=$protocole field="type"}}</th>
      <td>{{mb_field object=$protocole field="type" style="width: 15em;" onchange="ProtocoleDHE.onChangeType(this);"}}</td>
    </tr>
  {{/if}}
  <tr>
    <th></th>
    <td>
      {{mb_include module=planningOp template=inc_ufs_sejour_protocole object=$protocole}}
    </td>
  </tr>
    {{assign var=show_type_pec value="dPplanningOp CSejour fields_display show_type_pec"|gconf}}
    {{if $show_type_pec !== "hidden"}}
        {{if $show_type_pec === "mandatory"}}
            {{assign var=canNull value="false"}}
        {{else}}
            {{assign var=canNull value="true"}}
        {{/if}}
    <tr>
      <th>{{mb_label object=$protocole field="type_pec"}}</th>
        <td>
            <span onmouseover="ObjectTooltip.createDOM(this, 'type_pec_legend')">
               {{mb_field object=$protocole field="type_pec" typeEnum="radio" canNull=$canNull}}
            </span>
        </td>
    </tr>
  {{/if}}

  <tr>
    <th>{{mb_label object=$protocole field="hospit_de_jour"}} {{if "dPplanningOp CSejour hdj_seance"|gconf}}{{tr}}CSejour-Hdj / Seance{{/tr}}{{/if}}</th>
    <td>{{mb_field object=$protocole field="hospit_de_jour" typeEnum="radio"}}</td>
  </tr>

  <tr>
    <th>{{mb_label object=$protocole field="admission"}}</th>
    <td>{{mb_field object=$protocole field="admission" emptyLabel="Choose"}}</td>
  </tr>

  {{if "dPplanningOp CSejour fields_display show_facturable"|gconf}}
    <tr>
      <th>{{mb_label object=$protocole field="facturable"}}</th>
      <td>{{mb_field object=$protocole field="facturable" typeEnum="radio"}}</td>
    </tr>
  {{/if}}

  <tr>
    <th>{{mb_label object=$protocole field="RRAC" typeEnum="radio"}}</th>
    <td colspan="3">
      {{mb_field object=$protocole field="RRAC" typeEnum="radio"}}
    </td>
  </tr>

  {{if $show_field_circuit_ambu}}
    <tr id="circuit_ambu" style="{{if $protocole->type != "ambu"}}display: none;{{/if}}">
      <th>{{mb_label object=$protocole field="circuit_ambu" typeEnum="radio"}}</th>
      <td colspan="3">
        {{mb_field object=$protocole field="circuit_ambu" typeEnum="radio"}}
      </td>
    </tr>
  {{/if}}

  <tr id="row_codage_ngap_sejour"{{if $protocole->type != 'seances'}} style="display: none;"{{/if}}>
    <th>
      {{mb_label object=$protocole field=codage_ngap_sejour}}
      {{mb_field object=$protocole field=codage_ngap_sejour hidden=true}}
    </th>
    <td colspan="2" class="text">
      <span id="list_codage_ngap_sejour">{{$protocole->_codage_ngap_formatted}}</span>
      <button type="button" class="edit notext" onclick="ProtocoleDHE.codeProtocole('ngap');">
        Codage NGAP séjour
      </button>
    </td>
  </tr>

  {{if "appFineClient"|module_active && "appFineClient Sync allow_appfine_sync"|gconf}}
    {{mb_include module=appFineClient template=inc_button_pack object=$protocole}}
  {{/if}}

  <tr>
    <td colspan="2"><hr /></td>
  </tr>

  <tr>
    <td>{{mb_label object=$protocole field="convalescence"}}</td>
    <td>{{mb_label object=$protocole field="rques_sejour"}}</td>
  </tr>

  <tr>
    <td>{{mb_field object=$protocole field="convalescence" rows="3"}}</td>
    <td>{{mb_field object=$protocole field="rques_sejour" rows="3"}}</td>
  </tr>
  {{if "dPprescription"|module_active}}
    <tr>
      <th>
        <script>
          Main.add(function(){
            var form = getForm("editProtocole");
            var url = new Url("prescription", "httpreq_vw_select_protocole");
            var autocompleter = url.autoComplete(form.libelle_protocole, null, {
              minChars: 2,
              dropdown: true,
              width: "250px",
              valueElement: form.elements.protocole_prescription_chir_id,
              updateElement: function(selectedElement) {
                var node = $(selectedElement).down('.view');
                $V(form.libelle_protocole, node.innerHTML.replace("&lt;", "<").replace("&gt;",">"));
                if (autocompleter.options.afterUpdateElement)
                  autocompleter.options.afterUpdateElement(autocompleter.element, selectedElement);
              },
              callback: function(input, queryString){
                return (queryString + "&praticien_id=" + $V(form.chir_id) + "&function_id=" + $V(form.function_id));
              }
            });
          });
        </script>
        {{mb_label object=$protocole field="protocole_prescription_chir_id"}}
      </th>
      <td>
        <input type="text" name="libelle_protocole" id="editProtocole_libelle_protocole" class="autocomplete str"
               value="{{if $protocole->_id && $protocole->_ref_protocole_prescription_chir}}{{$protocole->_ref_protocole_prescription_chir->libelle}}{{/if}}"  style="width: 12em;"/>
        <input type="hidden" name="protocole_prescription_chir_id" value="{{$protocole->protocole_prescription_chir_id}}"
               onchange="ProtocoleDHE.fillClass(this.form.protocole_prescription_chir_id, this.form.protocole_prescription_chir_class);
                onSubmitFormAjax(this.form);"/>
        <input type="hidden" name="protocole_prescription_chir_class" value="{{$protocole->protocole_prescription_chir_class}}"/>
      </td>
    </tr>
  {{/if}}

  {{if $protocole->_id}}
    <tr>
      <th></th>
      <td>
        {{mb_include module=files template=inc_button_add_docitems context=$protocole type=sejour}}
      </td>
    </tr>
  {{/if}}
</table>
{{mb_include module=dPplanningOp template=inc_tooltip_type_pec}}
