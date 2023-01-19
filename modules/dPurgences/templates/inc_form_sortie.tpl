{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=mode_pec_med value=0}}
{{mb_default var=width_th value="30%"}}

{{assign var=config_interdire_mutation_hospit value="dPurgences CRPU interdire_mutation_hospit"|gconf}}

<script>
  Main.add(function() {
    var form = getForm("editSejour");
    var mode_sortie = form.elements.mode_sortie;

    {{if !$conf.dPplanningOp.CSejour.use_custom_mode_sortie}}
      changeOrientation(mode_sortie);
    {{/if}}
    var url = new Url('etablissement', 'ajax_autocomplete_etab_externe');
    url.addParam('field', 'etablissement_sortie_id');
    url.addParam('input_field', 'etablissement_sortie_id_view');
    url.addParam('view_field', 'nom');
    url.autoComplete(form.etablissement_sortie_id_view, null, {
      minChars: 0,
      method: 'get',
      select: 'view',
      dropdown: true,
      afterUpdateElement: function(field, selected) {
        var id = selected.getAttribute("id").split("-")[2];
        $V(form.etablissement_sortie_id, id);
        $V(form.etablissement_sortie_id_view, selected.down('span').innerHTML.trim());
        if ($('editRPU__destination')) {
          $V(getForm('editRPU')._destination, selected.down('span').get('destination'));
        }
      }
    });

    if (mode_sortie.value === 'mutation') {
      mode_sortie.onchange();
    }
  });

  updateModeSortie = function(select) {
    var selected = select.options[select.selectedIndex];
    var form = select.form;
    $V(form.elements.mode_sortie, selected.get("mode"));
    form.elements._date_deces.removeClassName("notNull");
    form.elements._date_deces_da.removeClassName("notNull");
    if ($V(form.elements.mode_sortie) == "deces") {
      form.elements._date_deces.addClassName("notNull");
      form.elements._date_deces_da.addClassName("notNull");
    }
  };
  updateLitMutation = function(element) {
    {{if $conf.dPurgences.use_blocage_lit}}
      var form = getForm('editSejour');
      if (form.mode_sortie.value == "mutation") {
        var url = new Url('urgences', 'ajax_refresh_lit');
        url.addParam('rpu_id'  , '{{$rpu->_id}}');
        url.addParam('sortie_reelle'  , element.value);
        url.requestUpdate("lit_sortie_transfert");
      }
    {{/if}}
  };
  //@todo a factoriser avec contraintes_RPU
  //Changement de l'orientation en fonction du mode sortie
  changeOrientation = function(element) {
    var orientation = getForm("editRPU").elements.orientation;
    var destination = getForm("editRPU").elements._destination;
      if (!orientation) {
      orientation = getForm("editRPUDest").elements.orientation;
      destination = getForm("editRPUDest").elements._destination;
    }
    var option_orientation = $A(orientation.options);
    var option_destination = $A(destination.options);
    var exclude = ["SCAM","PSA","REO"];
    var exclude_destination = ["6", "7"];
    switch ($V(element)) {
      case "normal":
        option_orientation.each(function(option) {
          //Si les options sont exclues on les désactive sinon on les réactive
          if (exclude.indexOf(option.value) === -1 && option.value !== "") {
            //Si l'option est sélectionnée et que dans ce cas, il n'est pas disponible, on met le sélectionne par défaut
            if (option.selected) {
              orientation.selectedIndex = 0;
            }
            option.disabled = true;
          }
          else {
            option.disabled = false;
          }
        });
        option_destination.each(function(option) {
          //Si les options sont exclues on les désactive sinon on les réactive
          if (exclude_destination.indexOf(option.value) === -1 && option.value !== "") {
            //Si l'option est sélectionnée et que dans ce cas, il n'est pas disponible, on met le sélectionne par défaut
            if (option.selected) {
              destination.selectedIndex = 0;
            }
            option.disabled = true;
          }
          else {
            option.disabled = false;
          }
        });
        break;
      case "mutation":
      case "transfert":
        option_orientation.each(function(option) {
          if (exclude.indexOf(option.value) !== -1) {
            if (option.selected) {
              orientation.selectedIndex = 0;
            }
            option.disabled = true;
          }
          else {
            option.disabled = false;
          }
        });
        option_destination.each(function(option) {
          if (exclude_destination.indexOf(option.value) !== -1) {
            if (option.selected) {
              destination.selectedIndex = 0;
            }
            option.disabled = true;
          }
          else {
            option.disabled = false;
          }
        });
        break;
      case "deces":
        option_orientation.each(function(option) {
            option.disabled = option.value !== "NA";
        });
        option_destination.each(function(option) {
          option.disabled = true;
        });
      break;
      default:
        option_orientation.each(function(option) {
          option.disabled = false;
        });
        option_destination.each(function(option) {
          option.disabled = false;
        });
    }
  };

  Fields = {
    init: function(mode_sortie) {
      ContraintesRPU.updateDestination(mode_sortie, true);
      ContraintesRPU.updateOrientation(mode_sortie, true);
      $('etablissement_sortie_transfert').setVisible(mode_sortie == "transfert");
      $('service_sortie_transfert'      ).setVisible(mode_sortie == "mutation");
      $$('.commentaires_sortie'         ).invoke('setVisible', (mode_sortie && mode_sortie != "normal"));
      $('date_deces'                    ).setVisible(mode_sortie == "deces");
      var date_deces = getForm("editSejour")._date_deces;
      if (mode_sortie != "deces") {
        $V(date_deces, "", false);
        $V(date_deces.previous().down("input"), "", false);
        date_deces.removeClassName("notNull");
      }
    }
  };
</script>

{{assign var=show_fields_sortie_rpu value="dPurgences CRPU show_fields_sortie_rpu"|gconf}}

<form name="editSejour" action="?" method="post"  onsubmit="return submitSejour()">
  <input type="hidden" name="dosql" value="do_sejour_aed" />
  <input type="hidden" name="m" value="planningOp" />
  <input type="hidden" name="del" value="0" />
  <input type="hidden" name="sejour_id" value="{{$sejour->_id}}" />
  <input type="hidden" name="type" value="{{$sejour->type}}" />

  <table class="form me-no-align me-no-box-shadow">
    {{if $can->edit && !$mode_pec_med}}
      <tr {{if !$ajax}} style="display: none" {{/if}}>
        <th>{{mb_label object=$sejour field=entree_reelle}}</th>
        <td colspan="3">
          {{mb_field object=$sejour field=entree_reelle hidden=true}}
          {{mb_value object=$sejour field=entree_reelle}}
        </td>
      </tr>

      {{if $show_fields_sortie_rpu && $rpu->sejour_id !== $rpu->mutation_sejour_id}}
        <tr>
          <th>{{mb_label object=$sejour field=sortie_reelle}}</th>
          <td colspan="3">
            {{mb_field object=$sejour field=sortie_reelle form=editSejour onchange="this.form.onsubmit();updateLitMutation(this);" register=true}}
          </td>
        </tr>
      {{/if}}
    {{/if}}

    {{if $show_fields_sortie_rpu}}
      <tr>
        <th style="width: {{$width_th}};">{{mb_label object=$sejour field="mode_sortie"}}</th>
        <td colspan="3">
          {{if $conf.dPplanningOp.CSejour.use_custom_mode_sortie && $list_mode_sortie|@count}}
            {{mb_field object=$sejour field=mode_sortie onchange="\$V(this.form._modifier_sortie, 0); Fields.init(this.value); this.form.onsubmit();" hidden=true}}
            <select name="mode_sortie_id" class="{{$sejour->_props.mode_sortie_id}}" style="width: 16em;" onchange="updateModeSortie(this)">
              <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
              {{foreach from=$list_mode_sortie item=_mode}}
                <option value="{{$_mode->_id}}" data-mode="{{$_mode->mode}}" {{if $sejour->mode_sortie_id == $_mode->_id}}selected{{/if}}>
                  {{$_mode}}
                </option>
              {{/foreach}}
            </select>
          {{elseif "dPurgences CRPU impose_create_sejour_mutation"|gconf}}
            <select name="mode_sortie" onchange="changeOrientation(this);Fields.init(this.value); this.form.onsubmit();">
              {{foreach from=$sejour->_specs.mode_sortie->_list item=_mode}}
                <option value="{{$_mode}}" {{if $sejour->mode_sortie == $_mode}}selected{{/if}}
                  {{if $_mode == "mutation"}}{{if $rpu->mutation_sejour_id}}selected{{else}}disabled{{/if}}{{/if}}>
                  {{tr}}CSejour.mode_sortie.{{$_mode}}{{/tr}}
                </option>
              {{/foreach}}
            </select>
          {{else}}
            {{assign var=mode_sortie value=$sejour->mode_sortie}}
            {{if $rpu->mutation_sejour_id}}
              {{assign var=mode_sortie value="mutation"}}
            {{/if}}
            <script>
              Main.add(function() {
                var mode_sortie = '{{$mode_sortie}}';
                if (mode_sortie === '') {
                  var form = getForm('editSejour');

                  // mode_sortie will already be valued as 'normal'
                  changeOrientation(form.mode_sortie);
                  Fields.init($V(form.mode_sortie));
                  form.onsubmit();
                }
              });
            </script>
            {{mb_field object=$sejour field="mode_sortie"
              onchange="Urgences.onchangeModeSortie(this, '`$config_interdire_mutation_hospit`', '`$rpu->mutation_sejour_id`');" value=$mode_sortie}}
          {{/if}}
          {{if !$rpu->mutation_sejour_id}}
            <input type="hidden" name="group_id" value="{{if $sejour->group_id}}{{$sejour->group_id}}{{else}}{{$g}}{{/if}}" />
          {{else}}
            <strong>
              <a href="?m=dPplanningOp&tab=vw_edit_sejour&sejour_id={{$rpu->mutation_sejour_id}}">
                Hospitalisation dossier {{mb_include module=planningOp template=inc_vw_numdos nda_obj=$rpu->_ref_sejour_mutation}}
               </a>
             </strong>
          {{/if}}
        </td>
      </tr>

      <tr id="etablissement_sortie_transfert" {{if $sejour->mode_sortie != "transfert"}} style="display:none;" {{/if}}>
        <th>{{mb_label object=$sejour field="etablissement_sortie_id"}}</th>
        <td colspan="3">
          {{mb_field object=$sejour field="etablissement_sortie_id" hidden=true onchange="this.form.onsubmit();"}}
          <input type="text" name="etablissement_sortie_id_view" value="{{$sejour->_ref_etablissement_transfert}}"/>
        </td>
      </tr>

      {{if $conf.dPurgences.use_blocage_lit}}
        {{mb_include module=urgences template=inc_form_sortie_lit}}
      {{/if}}

      <tr id="service_sortie_transfert" {{if !$rpu->mutation_sejour_id}} style="display:none;" {{/if}}>
        <th>{{mb_label object=$sejour field="service_sortie_id"}}</th>
        <td colspan="3">
          <input type="hidden" name="service_sortie_id" value="{{$sejour->service_sortie_id}}"
            class="autocomplete" onchange="this.form.onsubmit();" size="25"  />
          <input type="text" name="service_sortie_id_autocomplete_view" value="{{$sejour->_ref_service_mutation}}"
            class="autocomplete" onchange='if(!this.value){this.form["service_sortie_id"].value=""}'size="25"  />

          <script>
            Main.add(function(){
              var form = getForm("editSejour");
              var input = form.service_sortie_id_autocomplete_view;
              var url = new Url("system", "httpreq_field_autocomplete");
              url.addParam("class", "CSejour");
              url.addParam("field", "service_sortie_id");
              url.addParam("limit", 50);
              url.addParam("view_field", "nom");
              url.addParam("show_view", false);
              url.addParam("input_field", "service_sortie_id_autocomplete_view");
              url.addParam("wholeString", true);
              url.addParam("min_occurences", 1);
              url.autoComplete(input, "service_sortie_id_autocomplete_view", {
                minChars: 1,
                method: "get",
                select: "view",
                 dropdown: true,
                afterUpdateElement: function(field,selected){
                  $V(field.form["service_sortie_id"], selected.getAttribute("id").split("-")[2]);
                  var elementFormRPU = getForm("editRPU").elements;
                  if (!elementFormRPU) {
                    elementFormRPU = getForm("editRPUDest").elements;
                  }
                  var selectedData = selected.down(".data");
                  if (!elementFormRPU._destination.value) {
                    $V(elementFormRPU._destination, selectedData.get("default_destination"));
                  }
                  if (!elementFormRPU.orientation.value) {
                    $V(elementFormRPU.orientation, selectedData.get("default_orientation"));
                  }
                },
                callback: function(element, query){
                  query += "&where[group_id]={{if $sejour->group_id}}{{$sejour->group_id}}{{else}}{{$g}}{{/if}}";
                  var field = input.form.elements["cancelled"];
                  if (field) {
                    query += "&where[cancelled]=" + $V(field);  return query;
                  }
                  return null;
                }
              });
            });
          </script>

          <input type="hidden" name="cancelled" value="0" />
        </td>
      </tr>

      <tr id="date_deces" {{if $sejour->mode_sortie != "deces"}}style="display: none"{{/if}}>
        <th>{{mb_label class="CPatient" field="deces"}}</th>
        <td colspan="3">
          {{assign var=deces_notNull value=""}}
          {{if $sejour->mode_sortie == "deces"}}
            {{assign var=deces_notNull value="notNull"}}
          {{/if}}
          {{mb_field object=$sejour field="_date_deces" register=true onchange="this.form.onsubmit();" form="editSejour" class=$deces_notNull value=$sejour->_ref_patient->deces}}
        </td>
      </tr>

      {{if $rpu->sejour_id !== $rpu->mutation_sejour_id}}
        <tr>
          <th>{{mb_label object=$sejour field=transport_sortie}}</th>
          <td colspan="3">
            {{assign var=transport_sortie_null value="true"}}

            {{if "dPurgences CRPU impose_transport_sortie"|gconf && $app->_ref_user->isSecretaire()}}
              {{assign var=transport_sortie_null value="false"}}
            {{/if}}
            {{mb_field object=$sejour field=transport_sortie emptyLabel="Choose" canNull=$transport_sortie_null form=editSejour onchange="this.form.onsubmit();" register=true}}
          </td>
        </tr>
        <tr>
          <th class="text" style="text-align: right;">{{mb_label object=$sejour field=rques_transport_sortie}}</th>
          <td {{if !$mode_pec_med}}colspan="3"{{/if}}>
            {{mb_field object=$sejour field=rques_transport_sortie form=editSejour onchange="this.form.onsubmit();" register=true}}
          </td>
        {{if !$mode_pec_med}}
        </tr>
        {{/if}}
      {{/if}}

      {{if !$mode_pec_med || $rpu->sejour_id == $rpu->mutation_sejour_id}}
      <tr class="commentaires_sortie">
      {{/if}}
        <th {{if $mode_pec_med}}class="narrow text commentaires_sortie" style="text-align: right;"{{/if}}>{{mb_label object=$sejour field="commentaires_sortie"}}</th>
        <td {{if $mode_pec_med}}class="commentaires_sortie"{{else}}colspan="3"{{/if}}>
          {{mb_field object=$sejour field="commentaires_sortie" onchange="this.form.onsubmit();" form="editSejour"
            aidesaisie="validate: function() { form.onsubmit();},
                        resetSearchField: 0,
                        resetDependFields: 0,
                        validateOnBlur: 0" }}
          </td>
      </tr>
    {{/if}}

    <!-- Diagnostic Principal -->
    {{if !$mode_pec_med}}
    <tr id="dp_{{$sejour->_id}}">
      {{mb_include module=urgences template=inc_diagnostic_principal diagCanNull=true}}
    </tr>
    {{/if}}
  </table>
</form>
