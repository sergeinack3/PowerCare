{{*
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=urgences_active value="dPurgences"|module_active}}
{{if $urgences_active}}
  {{mb_script module=dPurgences script=contraintes_rpu ajax=true}}
  {{mb_script module=urgences script=urgences        ajax=true}}
{{/if}}

{{assign var=pass_to_confirm value="dPplanningOp CSejour pass_to_confirm"|gconf}}
{{assign var=use_cpi         value="dPplanningOp CSejour use_charge_price_indicator"|gconf}}
{{assign var=etab_externe_transfert_obligatory value="dPadmissions sortie etab_externe_transfert_obligatory"|gconf}}

{{assign var=form_name value="validerSortie`$sejour->_id`"}}
{{assign var=form_rpu_name value="editRpu`$sejour->_id`"}}

{{assign var=rpu value=$sejour->_ref_rpu}}
{{assign var=atu value=$sejour->_ref_consult_atu}}

{{assign var=class_sortie_reelle value=""}}
{{assign var=class_sortie_autorise value=""}}
{{assign var=class_mode_sortie value=""}}
{{assign var=is_praticien value=$app->_ref_user->isPraticien()}}
{{assign var=modify_sortie_reelle value=false}}

{{if $modify_sortie_prevue}}
  {{if $module == "dPurgences"}}
    {{if $rpu->sortie_autorisee}}
      {{assign var=class_sortie_autorise value="valid-field"}}
    {{else}}
      {{assign var=class_sortie_autorise value="inform-field"}}
    {{/if}}
  {{else}}
    {{if $sejour->confirme}}
      {{assign var=class_sortie_autorise value="valid-field"}}
    {{else}}
      {{assign var=class_sortie_autorise value="inform-field"}}
    {{/if}}
  {{/if}}
{{else}}
  {{assign var=modify_sortie_reelle value=true}}
  {{assign var=class_mode_sortie value="notNull"}}
  {{if $sejour->sortie_reelle}}
    {{assign var=class_sortie_reelle value="valid-field"}}
  {{else}}
    {{assign var=class_sortie_reelle value="inform-field"}}
  {{/if}}
{{/if}}

{{if "dPplanningOp CSejour required_mode_sortie"|gconf}}
  {{assign var=class_mode_sortie value="notNull"}}
{{/if}}

{{assign var=show_reglement_dh value=false}}
{{if $app->user_prefs.show_dh_admissions && $sejour->_ref_last_operation && $sejour->_ref_last_operation->_id
     && ($sejour->_ref_last_operation->depassement || $sejour->_ref_last_operation->depassement_anesth)}}
  {{assign var=show_reglement_dh value=true}}
{{/if}}

{{assign var=config_interdire_mutation_hospit value="dPurgences CRPU interdire_mutation_hospit"|gconf}}

<script>
  // Affichage du mode de traitement en fonction de la sortie réelle du patient
  toggleCharge = function() {
    {{if $use_cpi == "no"}}
      return;
    {{/if}}

    var type_sejour = "{{$sejour->type}}";

    var form = getForm("{{$form_name}}");

    var entree_reelle = $V(form.entree_reelle);
    var sortie_reelle = $V(form.sortie_reelle);
    var charge_id     = form.charge_id;

    if (!entree_reelle || !sortie_reelle || !charge_id) {
      return;
    }

    charge_id = charge_id.up("tr");

    charge_id.hide();

    entree_reelle = Date.fromDATETIME(entree_reelle);
    sortie_reelle = Date.fromDATETIME(sortie_reelle);

    // Durée en jours
    var duree = parseInt((sortie_reelle - entree_reelle) / (1000 * 24 * 3600));

    if ((duree > 1 && (type_sejour == "ambu" || type_sejour == "urg" {{if "dPurgences CRPU type_sejour"|gconf}}|| type_sejour == "consult"{{/if}})) || (duree == 1 && type_sejour != "ambu")) {
      charge_id.show();
    }
  };
  chooseEtabExterne = function(form) {
    {{if $etab_externe_transfert_obligatory}}
      var label_sortie = $('labelFor_'+'{{$form_name}}'+'_etablissement_sortie_id');
      if ($V(form.etablissement_sortie_id)) {
        label_sortie.removeClassName('notNull');
        label_sortie.addClassName('notNullOK');
      }
      else {
        label_sortie.addClassName('notNull');
        label_sortie.removeClassName('notNullOK');
      }
    {{/if}}
  };

  Main.add(function() {
    toggleCharge();

    var form = getForm('{{$form_name}}');

    // Mode de sortie à transfert si on est dans le cas
    if (window.Sejour && Sejour.current_group_id && Sejour.original_group_id && Sejour.current_group_id != Sejour.original_group_id) {
      if (form.mode_sortie) {
        $V(form.mode_sortie, 'transfert');
      }
    }

    if (form.mode_sortie.value === 'mutation') {
      form.mode_sortie.onchange();
    }
  });
</script>

{{if $rpu && $rpu->_id}}
  <form name="{{$form_name}}" method="post"
      onsubmit="return ContraintesRPU.checkObligatory('{{$rpu->_id}}', this,
        Admissions.confirmationSortie.curry(this, {{$modify_sortie_prevue}}, '{{$sejour->sortie_prevue}}',
        '{{"dPurgences CRPU impose_lit_service_mutation"|gconf}}',
        function() {
          {{if $atu && $atu->_id && $conf.dPurgences.valid_cotation_sortie_reelle}}
            onSubmitFormAjax(getForm('ValidCotation_{{$sejour->_id}}'), Control.Modal.close);
          {{else}}
            Control.Modal.close();
          {{/if}}
         }))">
{{else}}
<form name="{{$form_name}}" method="post"
      onsubmit="
          return Admissions.confirmationSortie(this, {{$modify_sortie_prevue}}, '{{$sejour->sortie_prevue}}', 0,
          Control.Modal.close);">
{{/if}}
  <input type="hidden" name="dosql" value="do_sejour_aed" />
  <input type="hidden" name="m" value="planningOp" />
  <input type="hidden" name="dtnow" value="{{$dtnow}}" />
  <input type="hidden" name="action_confirm" value="">
  {{mb_field object=$sejour field="sejour_id" hidden=true}}
  <input type="hidden" name="view_patient" value="{{$sejour->_ref_patient->_view}}">
  <input type="hidden" name="type" value="{{$sejour->type}}">
  <input type="hidden" name="del" value="0" />
  <input type="hidden" name="required_destination" value="{{"dPplanningOp CSejour required_destination"|gconf}}" />
  <input type="hidden" name="_sejours_enfants_ids" value="{{"|"|implode:$sejour->_sejours_enfants_ids}}" />
  <table class="form">
    <tr>
      <th class="title text" colspan="4">
        {{mb_include module=patients template=inc_view_ins_patient patient=$sejour->_ref_patient}}

        {{mb_include module=system template=inc_object_idsante400 object=$sejour->_ref_patient}}
        {{mb_include module=system template=inc_object_history object=$sejour->_ref_patient}}
        {{mb_include module=system template=inc_object_notes object=$sejour->_ref_patient}}
        {{$sejour->_ref_patient}} {{mb_include module=patients template=inc_vw_ipp ipp=$sejour->_ref_patient->_IPP}}
      </th>
    </tr>
    <tr>
      <th>{{mb_label object=$sejour field="entree_reelle"}}</th>
      <td>{{mb_field object=$sejour field="entree_reelle"}}</td>
      <th>{{mb_label object=$sejour field="entree_prevue"}}</th>
      <td>{{mb_field object=$sejour field="entree_prevue"}}</td>
    </tr>
    <tr>
      {{if $module != "dPurgences" || ($module == "dPurgences" && $rpu && $rpu->sejour_id !== $rpu->mutation_sejour_id)}}
        <th>
          {{if $module == "dPurgences" && $rpu && $rpu->mutation_sejour_id && $rpu->sejour_id !== $rpu->mutation_sejour_id}}
            <label>{{tr}}Csejour-sortie_reelle_mutation{{/tr}}</label>
           {{else}}
            {{mb_label object=$sejour field="sortie_reelle"}}
          {{/if}}
        </th>

        <td>
          {{assign var=date_time value=$sejour->sortie_reelle}}
          {{if (!$modify_sortie_prevue && !$sejour->sortie_reelle)}}
            {{assign var=date_time value=$dtnow}}
          {{/if}}

          {{mb_field object=$sejour field="sortie_reelle" form=$form_name register=$modify_sortie_reelle class=$class_sortie_reelle
                      onchange="Admissions.updateLitMutation(this.form); toggleCharge();" value="$date_time"}}</td>
      {{else}}
        <th></th>
        <td></td>
      {{/if}}
      <th>{{mb_label object=$sejour field="sortie_prevue"}}</th>
      <td>
        {{mb_field object=$sejour field="sortie_prevue" form=$form_name register=true}}

        {{if "planSoins"|module_active && !"planSoins general show_planned_exit"|gconf}}
          <script>
            Main.add(function() {
              var form = getForm("{{$form_name}}");
              $V(form.sortie_prevue_da, '');
            });
          </script>
        {{/if}}
      </td>
    </tr>
    <tr>
      {{me_form_field nb_cells=2 mb_object=$sejour mb_field=mode_sortie }}
        {{assign var=required_dest_when_transfer value='dPplanningOp CSejour required_dest_when_transfert'|gconf}}
        {{assign var=required_dest_when_mutation value='dPplanningOp CSejour required_dest_when_mutation'|gconf}}
        <script>
          Main.add(function() {
            var form = getForm("{{$form_name}}");
            {{if $urgences_active}}
              ContraintesRPU.changeOrientation(form);
            {{/if}}
            Admissions.required_dest_when_transfer = '{{$required_dest_when_transfer}}';
            Admissions.required_dest_when_mutation = '{{$required_dest_when_mutation}}';
            Admissions.etab_externe_transfert_obligatory = '{{$etab_externe_transfert_obligatory}}';
            Admissions.changeDestination(form);
            Admissions.changeSortie(form, '{{$sejour->_id}}');
          })
        </script>
        {{if $urgences_active}}
          {{assign var=onchange_mode_sortie value="Urgences.forbiddenMutationToHospitalization(this, '`$config_interdire_mutation_hospit`', '`$rpu->mutation_sejour_id`');ContraintesRPU.changeOrientation(this.form);Admissions.changeDestination(this.form);Admissions.changeSortie(this.form, '`$sejour->_id`')"}}
        {{else}}
          {{assign var=onchange_mode_sortie value="Admissions.changeDestination(this.form);Admissions.changeSortie(this.form, '`$sejour->_id`')"}}
        {{/if}}
        {{assign var=mode_sortie value=$sejour->mode_sortie}}
        {{if $sejour->service_sortie_id}}
          {{assign var=mode_sortie value="mutation"}}
        {{/if}}
        {{if $conf.dPplanningOp.CSejour.use_custom_mode_sortie && $list_mode_sortie|@count}}
          <script>
            applyModeSortie = function(elt) {
              var option_selected = elt.options[elt.selectedIndex];
              $V(elt.form.mode_sortie, option_selected.get("mode"));
              $V(elt.form.destination, option_selected.get("destination"));
              $V(elt.form.etablissement_sortie_id, option_selected.get("etab_externe_id"));
              $V(elt.form.etablissement_sortie_id_view, option_selected.get("etab_externe_view"));
              if (elt.form.orientation !== undefined) {
                $V(elt.form.orientation, option_selected.get('orientation'));
              }
            }
          </script>

          {{mb_field object=$sejour field=mode_sortie hidden=true class=$class_mode_sortie onchange="$onchange_mode_sortie"}}
          <select name="mode_sortie_id" class="{{$sejour->_props.mode_sortie_id}}" style="width: 16em;" onchange="applyModeSortie(this);">
            <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
            {{foreach from=$list_mode_sortie item=_mode}}
              <option value="{{$_mode->_id}}" {{if $sejour->mode_sortie_id == $_mode->_id}}selected{{/if}}
                      data-mode="{{$_mode->mode}}"
                      data-destination="{{$_mode->destination}}"
                      data-orientation="{{$_mode->orientation}}"
                      data-etab_externe_id="{{$_mode->etab_externe_id}}"
                      data-etab_externe_view="{{$_mode->_ref_etab_externe}}">
                {{$_mode}}
              </option>
            {{/foreach}}
          </select>
        {{elseif "dPurgences CRPU impose_create_sejour_mutation"|gconf}}
          <select name="mode_sortie" class="{{$class_mode_sortie}}" onchange="{{$onchange_mode_sortie}}">
            {{foreach from=$sejour->_specs.mode_sortie->_list item=_mode}}
              <option value="{{$_mode}}" {{if $sejour->mode_sortie == $_mode}}selected{{/if}}
                {{if $_mode == "mutation"}}{{if $rpu->mutation_sejour_id}}selected{{else}}disabled{{/if}}{{/if}}>
                {{tr}}CSejour.mode_sortie.{{$_mode}}{{/tr}}
              </option>
            {{/foreach}}
          </select>
        {{else}}
          {{if $rpu && $rpu->mutation_sejour_id}}
            {{assign var=mode_sortie value="mutation"}}
           {{else}}
            {{assign var=mode_sortie value=$sejour->mode_sortie}}
          {{/if}}
          <select name="mode_sortie" class="{{$class_mode_sortie}}" onchange="{{$onchange_mode_sortie}}">
            <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
            {{foreach from=$sejour->_specs.mode_sortie->_list item=_mode}}
              <option value="{{$_mode}}" {{if $mode_sortie == $_mode}}selected{{/if}}>
                  {{tr}}CSejour.mode_sortie.{{$_mode}}{{/tr}}
              </option>
            {{/foreach}}
          </select>
        {{/if}}
        {{if !$rpu || ($rpu && !$rpu->mutation_sejour_id)}}
          <input type="hidden" name="group_id" value="{{if $sejour->group_id}}{{$sejour->group_id}}{{else}}{{$g}}{{/if}}" />
        {{else}}
          <strong>
            <a href="?m=dPplanningOp&tab=vw_edit_sejour&sejour_id={{$rpu->mutation_sejour_id}}">
              Hospitalisation dossier {{mb_include module=planningOp template=inc_vw_numdos nda_obj=$rpu->_ref_sejour_mutation}}
            </a>
          </strong>
        {{/if}}
      {{/me_form_field}}
      <th style="width: 100px">
        {{if $module == "dPurgences" && $rpu && $rpu->mutation_sejour_id && $rpu->sejour_id !== $rpu->mutation_sejour_id}}
          <label>{{tr}}Csejour-confirme_mutation{{/tr}}</label>
        {{else}}
          {{mb_label object=$sejour field="confirme"}}
        {{/if}}
      </th>
      {{if $module == "dPurgences"}}
        <td>{{mb_value object=$rpu field="sortie_autorisee"}}</td>
      {{else}}
        <td>{{mb_field object=$sejour field="confirme" register=true form=$form_name class=$class_sortie_autorise onchange="\$('submitForm_sortie').disabled = this.value ? false : true"}}</td>
      {{/if}}
    </tr>
    {{if ($module == "dPurgences" && $use_cpi != "no") || $use_cpi == "obl"}}
      <tr>
        <th>{{mb_label class=CSejour field=charge_id}}</th>
        <td colspan="3">
          <select name="charge_id" class="ref {{if $use_cpi == "obl"}}notNull{{/if}}">
            <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
            {{foreach from='Ox\Mediboard\PlanningOp\CChargePriceIndicator::getList'|static_call:null item=_cpi}}
              <option value="{{$_cpi->_id}}" {{if $sejour->charge_id == $_cpi->_id}}selected{{/if}}>
                {{$_cpi|truncate:50:"...":false}}
              </option>
            {{/foreach}}
          </select>
        </td>
      </tr>
    {{/if}}
    <tr id="sortie_transfert_{{$sejour->_id}}" {{if $sejour->mode_sortie != "transfert"}} style="display:none;" {{/if}}>
      <th>{{mb_label object=$sejour field="etablissement_sortie_id"}}</th>
      <td colspan="3">
        {{mb_field object=$sejour field="etablissement_sortie_id" hidden=true onchange="chooseEtabExterne(this.form);"}}
        <input type="text" name="etablissement_sortie_id_view" value="{{$sejour->_ref_etablissement_transfert}}"/>

        <script>
          Main.add(function() {
            var url = new Url('etablissement', 'ajax_autocomplete_etab_externe');
            url.addParam('field', 'etablissement_sortie_id');
            url.addParam('input_field', 'etablissement_sortie_id_view');
            url.addParam('view_field', 'nom');
            url.autoComplete(getForm('{{$form_name}}').etablissement_sortie_id_view, null, {
              minChars: 0,
              method: 'get',
              select: 'view',
              dropdown: true,
              afterUpdateElement: function(field, selected) {
                var id = selected.getAttribute("id").split("-")[2];
                $V(getForm('{{$form_name}}').etablissement_sortie_id, id);
                $V(getForm('{{$form_name}}').etablissement_sortie_id_view, selected.down('span').innerHTML.trim());
                if ($('{{$form_name}}_destination')) {
                  $V(getForm('{{$form_name}}').destination, selected.down('span').get('destination'));
                }
              }
            });
          });
        </script>
      </td>
    </tr>

    <tbody id="lit_sortie_mutation_{{$sejour->_id}}" {{if $sejour->mode_sortie != "mutation"}} style="display:none;" {{/if}}>
      {{if $conf.dPurgences.use_blocage_lit}}
        <script>
          Main.add(
            function () {
              if (App.m == "dPurgences") {
                Admissions.updateLitMutation(getForm({{$form_name}}));
              }
            }
          )
        </script>
      {{/if}}
    </tbody>

    <tr id="sortie_service_mutation_{{$sejour->_id}}" {{if $sejour->mode_sortie != "mutation"}} style="display:none;" {{/if}}>
      <th>{{mb_label object=$sejour field="service_sortie_id"}}</th>
      <td colspan="3">
        <input type="hidden" name="service_sortie_id" value="{{$sejour->service_sortie_id}}"
               class="autocomplete" size="25"  />
        <input type="text" name="service_sortie_id_autocomplete_view" value="{{$sejour->_ref_service_mutation}}"
               class="autocomplete" onchange='if(!this.value){this.form["service_sortie_id"].value=""}' size="25" />
        <button type="button" class="notext erase"
                onclick="$V(this.form['service_sortie_id'],'');
                $V(this.form['service_sortie_id_autocomplete_view'],'')"></button>

        <script>
          Main.add(function(){
            var form = getForm({{$form_name}});
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
                if (field.form.lit_id) {
                  $V(field.form.lit_id, '', false);
                }
                var selectedData = selected.down(".data");
                if (selectedData.get("default_destination")) {
                  $V(form.destination, selectedData.get("default_destination"));
                }
                if (selectedData.get("default_orientation")) {
                  $V(form.orientation, selectedData.get("default_orientation"));
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
    </tr>
    <tr id="sortie_deces_{{$sejour->_id}}"{{if $sejour->mode_sortie != "deces"}} style="display:none;" {{/if}} data-date_deces="{{$sejour->_ref_patient->deces}}" data-date_deces_da="{{$sejour->_ref_patient->deces|date_format:$conf.datetime}}">
      <th>{{mb_label object=$sejour field="_date_deces"}}</th>
      <td colspan="3">
        {{mb_field object=$sejour field="_date_deces" value=$sejour->_ref_patient->deces register=true form=$form_name}}
      </td>
    </tr>
    {{if $module != "dPurgences" || ($module == "dPurgences" && $rpu && $rpu->sejour_id !== $rpu->mutation_sejour_id)}}
      <tbody id="transport_sortie_mutation_{{$sejour->_id}}">
        <tr>
          {{me_form_field nb_cells=4 mb_object=$sejour mb_field=transport_sortie}}
            {{assign var=transport_sortie_null value="true"}}

            {{if "dPurgences CRPU impose_transport_sortie"|gconf && $mode_sortie != "mutation" && $app->_ref_user->isSecretaire()}}
              {{assign var=transport_sortie_null value="false"}}

              <script>
                Main.add(function() {
                  Admissions.transport_sortie_mandatory = true;
                });
              </script>
            {{/if}}

            {{mb_field object=$sejour field="transport_sortie" emptyLabel="Choose" canNull=$transport_sortie_null}}
          {{/me_form_field}}
        </tr>
        <tr>
          {{me_form_field nb_cells=4 mb_object=$sejour mb_field=rques_transport_sortie field_class="me-form-group_fullw" }}
            {{mb_field object=$sejour field="rques_transport_sortie"}}
          {{/me_form_field}}
        </tr>
      </tbody>
    {{/if}}
    <tr>
      {{me_form_field nb_cells=4 mb_object=$sejour mb_field=commentaires_sortie field_class="me-form-group_fullw"}}
        {{mb_field object=$sejour field="commentaires_sortie" form=$form_name
        aidesaisie="resetSearchField: 0, resetDependFields: 0, validateOnBlur: 0"}}
      {{/me_form_field}}
    </tr>
    {{assign var=destination_notnull value=""}}
    {{assign var=destination_value value=$sejour->destination}}
    {{if "dPplanningOp CSejour required_destination"|gconf && $app->_ref_user->isSecretaire()}}
      {{assign var=destination_notnull value="notNull"}}
      {{if !$sejour->destination && ($sejour->mode_sortie == "normal" || $sejour->mode_sortie == "deces")}}
        {{assign var=destination_value value=0}}
      {{/if}}
    {{/if}}
    <tr>
      {{me_form_field nb_cells=4 mb_object=$sejour mb_field=destination class=$destination_notnull}}
        {{mb_field object=$sejour field="destination" emptyLabel="Choose" class=$destination_notnull value=$destination_value}}
      {{/me_form_field}}
    </tr>

    {{assign var=list_mode_pec value='Ox\Mediboard\PlanningOp\CModePECSejour::listModes'|static_call:true}}
    {{if $list_mode_pec|@count}}
      <tr>
        <th>{{mb_label object=$sejour field=mode_pec_id}}</th>
        <td colspan="3">
          <select name="mode_pec_id" style="width: 16em;">
            <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
            {{foreach from=$list_mode_pec item=_mode_pec}}
              <option value="{{$_mode_pec->_id}}"
                      {{if (!$sejour->mode_pec_id && $_mode_pec->default)
                      || ($sejour->mode_pec_id == $_mode_pec->_id)}}selected{{/if}}
                >{{$_mode_pec}}</option>
            {{/foreach}}
          </select>
        </td>
      </tr>
    {{/if}}
    {{assign var=list_mode_destination value='Ox\Mediboard\PlanningOp\CModeDestinationSejour::listModes'|static_call:true}}
    {{if $list_mode_destination|@count}}
      <tr>
        <th>{{mb_label object=$sejour field=mode_destination_id}}</th>
        <td colspan="3">
          <select name="mode_destination_id" style="width: 16em;">
            <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
            {{foreach from=$list_mode_destination item=_mode_destination}}
              <option value="{{$_mode_destination->_id}}"
                      {{if (!$sejour->mode_destination_id && $_mode_destination->default)
                      || ($sejour->mode_destination_id == $_mode_destination->_id)}}selected{{/if}}
                >{{$_mode_destination}}</option>
            {{/foreach}}
          </select>
        </td>
      </tr>
    {{/if}}

    {{if $rpu && $rpu->_id}}

      {{assign var=orientation_null value="true"}}
      {{if "dPurgences CRPU required_orientation"|gconf}}
        {{assign var=orientation_null value="false"}}
      {{/if}}

      <tr>
        <th>{{mb_label object=$rpu field="orientation"}}</th>
        <td colspan="3">{{mb_field object=$rpu field="orientation" emptyLabel="Choose" canNull=$orientation_null onchange="\$V(getForm('$form_rpu_name').orientation, \$V(this));"}}</td>
      </tr>
    {{/if}}

    {{if $app->user_prefs.show_dh_admissions}}
      <tr>
        <th>
          {{mb_label object=$sejour field=frais_sejour}}
        </th>
        <td>
          {{mb_field object=$sejour field=frais_sejour}}
        </td>
        <th>
          {{mb_label object=$sejour field=reglement_frais_sejour}}
        </th>
        <td>
          {{mb_field object=$sejour field=reglement_frais_sejour}}
        </td>
      </tr>
    {{/if}}

    {{if $show_reglement_dh}}
      <tr data-operation_id="{{$sejour->_ref_last_operation->_id}}">
        {{if $sejour->_ref_last_operation->depassement}}
          <th>
            {{mb_label object=$sejour->_ref_last_operation field=reglement_dh_chir}}
          </th>
          <td>
            {{mb_field object=$sejour->_ref_last_operation field=reglement_dh_chir}} ({{mb_value object=$sejour->_ref_last_operation field=depassement}})
          </td>
        {{/if}}
        {{if $sejour->_ref_last_operation->depassement_anesth}}
          <th>
            {{mb_label object=$sejour->_ref_last_operation field=reglement_dh_anesth}}
          </th>
          <td>
            {{mb_field object=$sejour->_ref_last_operation field=reglement_dh_anesth}} ({{mb_value object=$sejour->_ref_last_operation field=depassement_anesth}})
          </td>
        {{/if}}
        {{if $sejour->_ref_last_operation->depassement || !$sejour->_ref_last_operation->depassement_anesth}}
          <td colspan="2"></td>
        {{/if}}
      </tr>
    {{/if}}

    {{if !$modify_sortie_prevue}}
      <tr>
        <td colspan="4" class="button">
          {{if "trajectoire"|module_active}}
            <script>
              Main.add(function() {
                var url = new Url('trajectoire', 'ajax_trajectoire_redirect');
                url.addParam('patient_id', '{{$sejour->patient_id}}');
                url.addParam('sejour_id', '{{$sejour->_id}}');
                url.requestUpdate('trajectoire_button');
              });
            </script>

            <span id="trajectoire_button"></span>
          {{/if}}
          <button type="button" class="close singleclick"
                onclick="Admissions.annulerSortie(this.form, Control.Modal.close)">
            {{tr}}Cancel{{/tr}}
            {{mb_label object=$sejour field=sortie}}
          </button>
          <button type="submit" class="save singleclick autoriser_sortie">
            {{tr}}Validate{{/tr}}
            {{mb_label object=$sejour field=sortie}}
          </button>
        </td>
      </tr>
    {{else}}
      <tr>
        <td colspan="4" class="button">
          {{if "trajectoire"|module_active}}
            <script>
              Main.add(function() {
                var url = new Url('trajectoire', 'ajax_trajectoire_redirect');
                url.addParam('patient_id', '{{$sejour->patient_id}}');
                url.addParam('sejour_id', '{{$sejour->_id}}');
                url.requestUpdate('trajectoire_button');
              });
            </script>

            <span id="trajectoire_button"></span>
          {{/if}}

          {{mb_field object=$sejour field="confirme_user_id" hidden=true}}
          <button type="submit" id="submitForm_sortie" class="save" onclick="if (!$V(this.form.confirme_user_id) && $V(this.form.confirme)) {
            $V(this.form.confirme_user_id, '{{$app->user_id}}');
            }">
            {{tr}}Save{{/tr}}
          </button>
          {{if $sejour->confirme}}
            <button type="button" class="cancel singleclick"
                    onclick="{{if !$is_praticien}}
                                this.form.action_confirm.value = 0;
                                Admissions.askconfirm('{{$sejour->_id}}');
                             {{else}}
                               $V(this.form.confirme, ''); $V(this.form.confirme_user_id, '');this.form.onsubmit();
                             {{/if}}">
              {{tr}}canceled_exit{{/tr}}
            </button>
          {{else}}
            <button type="button" class="tick singleclick"
                    onclick="{{if !$is_praticien}}
                      $V(this.form.action_confirm, 1);
                      Admissions.askconfirm('{{$sejour->_id}}');
                    {{else}}
                      if (!$V(this.form.confirme)) {
                        var sortie_prevue = $V(this.form.sortie_prevue);
                        var sortie_reelle = $V(this.form.sortie_reelle);
                        var sortie = sortie_reelle ? sortie_reelle : sortie_prevue;
                        $V(this.form.confirme, sortie);
                      }
                      $V(this.form.confirme_user_id, '{{$app->user_id}}');
                      this.form.onsubmit();
                    {{/if}}">
              {{tr}}allowed_exit{{/tr}}
            </button>
          {{/if}}
        </td>
      </tr>
    {{/if}}
  </table>
</form>


{{foreach from=$sejour->_ref_naissances item=_naissance}}
  {{assign var=sejour_enfant value=`$_naissance->_ref_sejour_enfant`}}
  {{assign var=form_name_enfant value="validerSortieEnfant`$sejour_enfant->_id`"}}
  {{mb_include module=admissions template=inc_edit_sortie_sejour_form ext_sejour=$sejour_enfant form_name=$form_name_enfant}}
{{/foreach}}

{{if $sejour_mere->_id}}
  {{assign var=ext_sejour value=$sejour_mere}}
  {{assign var=form_name value="validerSortieMere"}}
  {{mb_include module=admissions template=inc_edit_sortie_sejour_form}}
  {{foreach from=$ext_sejour->_ref_naissances item=_naissance}}
    {{assign var=sejour_enfant value=`$_naissance->_ref_sejour_enfant`}}
    {{assign var=form_name value="validerSortieEnfant`$sejour_enfant->_id`"}}
    {{mb_include module=admissions template=inc_edit_sortie_sejour_form ext_sejour=$sejour_enfant}}
  {{/foreach}}
{{/if}}

{{if $rpu && $rpu->_id}}
  <form name="{{$form_rpu_name}}" method="post" onsubmit="return onSubmitFormAjax(this)">
    {{mb_key object=$rpu}}
    {{mb_class object=$rpu}}
    <input type="hidden" name="_validation" value="1">
    <input type="hidden" name="del" value="0" />
    {{mb_field object=$rpu field="orientation" hidden=true onchange=this.form.onsubmit()}}
  </form>
{{/if}}

{{if $atu && $atu->_id && $conf.dPurgences.valid_cotation_sortie_reelle}}
  <form name="ValidCotation_{{$sejour->_id}}" action="" method="post">
    <input type="hidden" name="dosql" value="do_consultation_aed" />
    <input type="hidden" name="m" value="dPcabinet" />
    <input type="hidden" name="del" value="0" />
    <input type="hidden" name="consultation_id" value="{{$atu->_id}}" />
    <input type="hidden" name="valide" value="1" />
  </form>
{{/if}}

{{if $show_reglement_dh}}
  <form name="editDepassementIntervSortie-{{$sejour->_ref_last_operation->_id}}" action="?" method="post" onsubmit="return onSubmitFormAjax(this);">
    {{mb_class object=$sejour->_ref_last_operation}}
    {{mb_key object=$sejour->_ref_last_operation}}
    {{mb_field object=$sejour->_ref_last_operation field=reglement_dh_chir hidden=true}}
    {{mb_field object=$sejour->_ref_last_operation field=reglement_dh_anesth hidden=true}}
  </form>
{{/if}}

<div id="confirmSortieModal_{{$sejour->_id}}" style="display: none;">
  <form name="confirmSortie_{{$sejour->_id}}" method="post" action="?m=system&a=ajax_password_action"
        onsubmit="return onSubmitFormAjax(this, {onComplete: Control.Modal.close, useFormAction: true})">
    <input type="hidden" name="callback" value="Admissions.afterConfirmPassword.curry({{$sejour->_id}})" />
    <input type="hidden" name="user_id" class="notNull" value="{{$sejour->_ref_praticien->_id}}" />
    <table class="form">
      <tr>
        <th class="title" colspan="2">
          {{tr}}Confirm-allowed-exit{{/tr}}
        </th>
      </tr>
      {{if !$pass_to_confirm}}
        <tr>
          <td colspan="2" class="button">
            <button type="button" class="tick"
                    onclick="Control.Modal.close();Admissions.afterConfirmPassword({{$sejour->_id}}, '{{$app->_ref_user->_id}}');">
              {{$app->_ref_user}}
            </button>
            <br/>
            OU
          </td>
        </tr>
      {{/if}}
      <tr>
        <th>{{tr}}CSejour-_nomPraticien{{/tr}}</th>
        <td>
          <input type="text" name="_user_view" class="autocomplete" value="{{$sejour->_ref_praticien}}" />
          <script>
            Main.add(function() {
              var form = getForm("confirmSortie_{{$sejour->_id}}");
              new Url("mediusers", "ajax_users_autocomplete")
                .addParam("input_field", form._user_view.name)
                .addParam("praticiens", 1)
                .autoComplete(form._user_view, null, {
                minChars: 0,
                method: "get",
                select: "view",
                dropdown: true,
                width: '200px',
                afterUpdateElement: function(field, selected) {
                  $V(form._user_view, selected.down('.view').innerHTML);
                  var id = selected.getAttribute("id").split("-")[2];
                  $V(form.user_id, id);
                }
              });
            });
          </script>
        </td>
      </tr>
      <tr>
        <th>
          <label for="user_password">{{tr}}Password{{/tr}}</label>
        </th>
        <td>
          <input type="password" name="user_password" class="notNull password str" />
        </td>
      </tr>
      <tr>
        <td colspan="2" class="button">
          <button type="submit" class="tick singleclick">{{tr}}Validate{{/tr}}</button>
          <button type="button" class="cancel singleclick" onclick="Control.Modal.close();">{{tr}}Cancel{{/tr}}</button>
        </td>
      </tr>
    </table>
  </form>
</div>
