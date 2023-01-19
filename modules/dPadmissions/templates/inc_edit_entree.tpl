{{*
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{mb_script module=admissions script=admissions ajax=$ajax}}

{{assign var="form_name" value="editAdmFrm`$sejour->_id`"}}
{{assign var="entree_reelle" value=$sejour->entree_reelle}}
{{assign var=provenance_transfert_obligatory value="dPadmissions admission provenance_transfert_obligatory"|gconf}}
{{assign var=provenance_mutation_obligatory value="dPadmissions admission provenance_mutation_obligatory"|gconf}}
{{assign var=date_entree_transfert_obligatory value="dPadmissions admission date_entree_transfert_obligatory"|gconf}}
{{assign var=etab_externe_transfert_obligatory value="dPadmissions admission etab_externe_transfert_obligatory"|gconf}}
{{assign var=show_reglement_dh value=false}}

{{if $app->user_prefs.show_dh_admissions && $sejour->_ref_last_operation && $sejour->_ref_last_operation->_id
     && ($sejour->_ref_last_operation->depassement || $sejour->_ref_last_operation->depassement_anesth)}}
  {{assign var=show_reglement_dh value=true}}
{{/if}}

<script>
  emptyFields = function() {
    var form = getForm('{{$form_name}}');
    $V(form.entree_reelle, '');
    $V(form.service_entree_id, '');
    $V(form._modifier_entree, '0');
    form.onsubmit();
  };

  admettre = function() {
    var form = getForm('{{$form_name}}');

    // with idex
    var idex = $('idex_sejour_{{$sejour->_id}}');
    var nb_idex_not_ok = 0;
    if (idex) {
      $(idex).select('form').each(function(elt) {
        if (elt.id400 && !checkForm(elt)) {
          nb_idex_not_ok = 1;
          return false;
        }
        elt.onsubmit();
      });
    }

    // do idex must defined ?
    var must_be_define_idex = {{"dPsante400 CIdSante400 admit_ipp_nda_obligatory"|gconf}};
    if (nb_idex_not_ok && must_be_define_idex) {
      return;
    }

    {{if !$entree_reelle}}
      var date_entree_reelle = $V(form.entree_reelle) === 'now' ? new Date() : Date.fromDATETIME($V(form.entree_reelle));
      var date_entree_prevue = Date.fromDATETIME($V(form.entree_prevue));
      if ((date_entree_reelle.toDATE() === date_entree_prevue.toDATE()) ||
           confirm('La date enregistrée d\'admission est différente de la date prévue, souhaitez vous confimer l\'admission du patient ?')) {
        form.onsubmit();
        {{if $show_reglement_dh}}
          getForm('editDepassementIntervEntree-{{$sejour->_ref_last_operation->_id}}').onsubmit();
        {{/if}}
      }
    {{else}}
      form.onsubmit();
      {{if $show_reglement_dh}}
        getForm('editDepassementIntervEntree-{{$sejour->_ref_last_operation->_id}}').onsubmit();
      {{/if}}
    {{/if}}
  };

  showSecondary = function() {
    $$('.togglisable_tr').invoke("hide");
    $('empty_entree_id_{{$sejour->_id}}').hide();

    var form = getForm('{{$form_name}}');
    var val = $V(form.mode_entree);
    if (val == 7 || val == 0) {
      //Transfert
      $('etablissement_entree_id_{{$sejour->_id}}').show();
      $('provenance_entree_id_{{$sejour->_id}}').show();
      $('transfert_entree_reelle_{{$sejour->_id}}').show();
    }
    else if (val == 6) {
      //Mutation
      $('service_entree_id_{{$sejour->_id}}').show();
      $('etablissement_entree_id_{{$sejour->_id}}').show();
      $('provenance_entree_id_{{$sejour->_id}}').show();
      $('transfert_entree_reelle_{{$sejour->_id}}').hide();
    }
    else if (val == 8) {
      //Domicile
      $('provenance_entree_id_{{$sejour->_id}}').show();
    }
    else {
      //Nouveau Né
      $('empty_entree_id_{{$sejour->_id}}').show();
    }

    if (val != 6) {
      $V(form.service_entree_id, '');
    }
    if (val != 7 && val != 0) {
      $V(form.etablissement_entree_id, '', false);
      $V(form.etablissement_entree_id_view, '');
      $V(form.date_entree_reelle_provenance, '', false);
      $V(form.date_entree_reelle_provenance_da, '');
    }

    {{if $provenance_transfert_obligatory}}
      var provenance = $(form.provenance);
      if (val == 7) {
        provenance.addClassName('notNull');
        $('labelFor_'+'{{$form_name}}'+'_provenance').addClassName('notNull');
      }
      else {
        provenance.removeClassName('notNull');
        $('labelFor_'+'{{$form_name}}'+'_provenance').removeClassName('notNull');
      }
    {{/if}}

    {{if $provenance_mutation_obligatory}}
      var provenance = $(form.provenance);
      if (val == 6) {
        provenance.addClassName('notNull');
        $('labelFor_'+'{{$form_name}}'+'_provenance').addClassName('notNull');
      }
      else {
        provenance.removeClassName('notNull');
        $('labelFor_'+'{{$form_name}}'+'_provenance').removeClassName('notNull');
      }
    {{/if}}

    {{if $date_entree_transfert_obligatory}}
      var date_tranfert = $(form.date_entree_reelle_provenance);
      if (val == 7) {
        date_tranfert.addClassName('notNull');
        $('labelFor_'+'{{$form_name}}'+'_date_entree_reelle_provenance').addClassName('notNull');
      }
      else {
        date_tranfert.removeClassName('notNull');
        $('labelFor_'+'{{$form_name}}'+'_date_entree_reelle_provenance').removeClassName('notNull');
      }
    {{/if}}

    {{if $etab_externe_transfert_obligatory}}
      var etab_externe = $(form.etablissement_entree_id);
      if (val == 7) {
        etab_externe.addClassName('notNull');
        $('labelFor_'+'{{$form_name}}'+'_etablissement_entree_id').addClassName('notNull');
      }
      else {
        etab_externe.removeClassName('notNull');
        $('labelFor_'+'{{$form_name}}'+'_etablissement_entree_id').removeClassName('notNull');
      }
    {{/if}}
    Admissions.changeProvenance(form);
  };

  updateModeEntree = function(select) {
    var selected = select.options[select.selectedIndex];
    var form = select.form;
    $V(form.elements.mode_entree, selected.get("mode"));
    $V(form.elements.provenance, selected.get("provenance"));
  };

  chooseEtabExterne = function(form) {
    {{if $etab_externe_transfert_obligatory}}
      var label_entree = $('labelFor_'+'{{$form_name}}'+'_etablissement_entree_id');
      if ($V(form.etablissement_entree_id)) {
        label_entree.removeClassName('notNull');
        label_entree.addClassName('notNullOK');
      }
      else {
        label_entree.addClassName('notNull');
        label_entree.removeClassName('notNullOK');
      }
    {{/if}}
    if (window.changeEtablissementId) {
      changeEtablissementId(form);
    }
  };

  resetProvenance = function (form) {
    $V(form.provenance, '', false);
  };

  Main.add(function() {
    showSecondary();
    $('provenance_entree_id_{{$sejour->_id}}').show();
  });
</script>

<h2>Admission</h2>
<form name="{{$form_name}}" action="?" method="post" onsubmit="return onSubmitFormAjax(this, {onComplete: Control.Modal.close})">
  <input type="hidden" name="m" value="planningOp" />
  <input type="hidden" name="dosql" value="do_sejour_aed" />
  {{mb_key object=$sejour}}
  <input type="hidden" name="patient_id" value="{{$sejour->patient_id}}" />
  {{mb_field object=$sejour field=sortie_prevue hidden=true}}
  {{mb_field object=$sejour field=type          hidden=true}}

  <table class="form">
    <tr>
      <th>{{mb_label object=$sejour field=entree_reelle}}</th>
      <td>
        {{if !$entree_reelle}}
          {{mb_field object=$sejour field=entree_reelle form=$form_name register=true class="notNull" value="now"}}
        {{else}}
          {{mb_field object=$sejour field=entree_reelle form=$form_name register=true}}
        {{/if}}
      </td>
      <th>{{mb_label object=$sejour field=entree_prevue}}</th>
      <td>{{mb_field object=$sejour field=entree_prevue}}</td>
    </tr>

    <input type="hidden" name="_modifier_entree" value="1" />

    {{assign var=_mode_entree_prop value=$sejour->_props.mode_entree}}
    {{if "dPplanningOp CSejour required_mode_entree"|gconf}}
      {{assign var=_mode_entree_prop value="$_mode_entree_prop notNull"}}
    {{/if}}

    <tr>
      {{if $conf.dPplanningOp.CSejour.use_custom_mode_entree && $list_mode_entree|@count}}
        <th>
          {{mb_label object=$sejour field=mode_entree prop=$_mode_entree_prop}}
        </th>
        <td>
          {{mb_field object=$sejour field=mode_entree hidden=true prop=$_mode_entree_prop onchange="showSecondary(); resetProvenance(this.form);"}}

          <select name="mode_entree_id" class="{{$sejour->_props.mode_entree_id}}" style="width: 15em;" onchange="updateModeEntree(this)">
            <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
            {{foreach from=$list_mode_entree item=_mode}}
              <option value="{{$_mode->_id}}" data-mode="{{$_mode->mode}}" data-provenance="{{$_mode->provenance}}"
                      {{if $sejour->mode_entree_id == $_mode->_id}}selected{{/if}}>
                {{$_mode}}
              </option>
            {{/foreach}}
          </select>
        </td>
      {{else}}
        <th>
          {{mb_label object=$sejour field=mode_entree prop=$_mode_entree_prop}}
        </th>
        <td>
          {{mb_field object=$sejour field=mode_entree onchange="showSecondary(); resetProvenance(this.form);" typeEnum=radio prop=$_mode_entree_prop}}
        </td>
      {{/if}}
      {{if $show_list_uf}}
        <th>{{mb_label object=$sejour field=uf_soins_id prop="`$sejour->_props.uf_soins_id` notNull"}}</th>
        <td>
          <select name="uf_soins_id" class="{{$sejour->_props.uf_soins_id}} notNull">
            <option value=""> &mdash; </option>
            {{foreach from=$list_uf_soin item=_uf}}
              <option value="{{$_uf->_id}}" {{if $_uf->_id == $sejour->uf_soins_id}}selected{{/if}}>{{$_uf}}</option>
            {{/foreach}}
          </select>
        </td>
      {{else}}
        <td colspan="2"></td>
      {{/if}}
    </tr>

    {{if $show_list_uf_med}}
      <tr>
        <td colspan="2"></td>
        <th>{{mb_label object=$sejour field=uf_medicale_id prop="`$sejour->_props.uf_medicale_id` notNull"}}</th>
        <td>
          <select name="uf_medicale_id" class="{{$sejour->_props.uf_medicale_id}} notNull">
            <option value=""> &mdash; </option>
            {{foreach from=$list_uf_med item=_uf}}
              <option value="{{$_uf->_id}}" {{if $_uf->_id == $sejour->uf_medicale_id}}selected{{/if}}>{{$_uf}}</option>
            {{/foreach}}
          </select>
        </td>
      </tr>
    {{/if}}

    <tr id="empty_entree_id_{{$sejour->_id}}">
      <td colspan="4">&nbsp;</td>
    </tr>

    <tbody id="etablissement_entree_id_{{$sejour->_id}}" style="display: none" class="togglisable_tr">
      <tr id="transfert_entree_reelle_{{$sejour->_id}}">
        <th>{{mb_label object=$sejour field=etablissement_entree_id}}</th>
        <td>
          {{mb_field object=$sejour field="etablissement_entree_id" onchange="chooseEtabExterne(this.form);" hidden=true}}
          <input type="text" name="etablissement_entree_id_view" value="{{$sejour->_ref_etablissement_provenance}}" class=""/>

          <script type="text/javascript">
            Main.add(function() {
              var url = new Url('etablissement', 'ajax_autocomplete_etab_externe');
              url.addParam('field', 'etablissement_entree_id');
              url.addParam('input_field', 'etablissement_entree_id_view');
              url.addParam('view_field', 'nom');
              url.autoComplete(getForm('{{$form_name}}').etablissement_entree_id_view, null, {
                minChars: 0,
                method: 'get',
                select: 'view',
                dropdown: true,
                afterUpdateElement: function(field, selected) {
                  var form = getForm('{{$form_name}}');
                  $(form.provenance).removeClassName('notNull');
                  $(form.date_entree_reelle_provenance).removeClassName('notNull');
                  var id = selected.getAttribute("id").split("-")[2];
                  $V(form.etablissement_entree_id_view, selected.down('span').innerHTML.trim());
                  $V(form.etablissement_entree_id, id);
                  showSecondary();
                  resetProvenance(form);
                  if ($('{{$form_name}}_provenance')) {
                    $V(form.provenance, selected.down('span').get('provenance'));
                  }
                }
              });
            });
          </script>
        </td>

        {{mb_ternary var=notnull_entree test=$date_entree_transfert_obligatory value="notNull" other=""}}
        <th>{{mb_label object=$sejour field=date_entree_reelle_provenance class=$notnull_entree}}</th>
        <td>{{mb_field object=$sejour field=date_entree_reelle_provenance class=$notnull_entree form=$form_name register=true}}</td>
      </tr>
    </tbody>

    <tbody id="provenance_entree_id_{{$sejour->_id}}" style="display: none" class="togglisable_tr">
      <tr>
        {{mb_ternary var=notnull_provenance test=$provenance_transfert_obligatory value="notNull" other=""}}
        <th>{{mb_label object=$sejour field=provenance class=$notnull_provenance}}</th>
          <td>{{mb_field object=$sejour field=provenance class=$notnull_provenance emptyLabel="Choose" style="width: 15em;"}}</td>
        <td colspan="2"></td>
      </tr>
    </tbody>

    <tr class="togglisable_tr" id="service_entree_id_{{$sejour->_id}}">
      <th>{{mb_label object=$sejour field=service_entree_id}}</th>
      <td colspan="3">
        <select name="service_entree_id">
          <option value="">{{tr}}Choose{{/tr}}</option>
          {{foreach from=$services item=_service}}
            <option value="{{$_service->_id}}" {{if $_service->_id == $sejour->service_entree_id}}selected="selected" {{/if}}>{{$_service}}</option>
          {{/foreach}}
        </select>
      </td>
    </tr>
    {{if "dPplanningOp CSejour use_charge_price_indicator"|gconf == "obl"}}
      <tr>
        <th>{{mb_label object=$sejour field=charge_id}}</th>
        <td colspan="3">
          <select name="charge_id" class="ref notNull">
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
  </table>
</form>

{{if "dPsante400"|module_active && "dPsante400 CIdSante400 add_ipp_nda_manually"|gconf}}
  <hr/>
  <h2>{{tr}}mod-dPsante400-tab-ajax_edit_manually_ipp_nda{{/tr}}</h2>
  {{assign var=ipp value=$sejour->_ref_patient->_ref_IPP}}
  {{assign var=nda value=$sejour->_ref_NDA}}
  {{unique_id var=unique_ipp}}
  {{unique_id var=unique_nda}}

  <div id="idex_sejour_{{$sejour->_id}}">
    {{mb_include module=dPsante400 template=inc_form_ipp_nda idex=$ipp object=$sejour->_ref_patient field=_IPP unique=$unique_ipp}}
    {{mb_include module=dPsante400 template=inc_form_ipp_nda idex=$nda object=$sejour field=_NDA unique=$unique_nda}}
  </div>
{{/if}}

{{if "eSatis"|module_active}}
  {{mb_include module="eSatis" template="inc_esatis_consent"}}
{{/if}}

{{if "dPpatients CPatient alert_email_a_jour"|gconf || "dPpatients CPatient alert_telephone_a_jour"|gconf}}
  <hr/>
  {{mb_include module=patients template=vw_maj_email_tel_patient patient=$sejour->_ref_patient}}
{{/if}}

{{if $show_reglement_dh}}
  <hr/>
  <form name="editDepassementIntervEntree-{{$sejour->_ref_last_operation->_id}}" action="?" method="post" onsubmit="return onSubmitFormAjax(this);">
    {{mb_class object=$sejour->_ref_last_operation}}
    {{mb_key object=$sejour->_ref_last_operation}}

    <table class="form">
      <tr>
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
      </tr>
    </table>
  </form>
{{/if}}

<hr class="me-no-display" />

<p style="text-align: center">
  <button class="{{if !$entree_reelle}}tick{{else}}save{{/if}} singleclick" type="button" onclick="admettre();">
    {{if !$entree_reelle}}{{tr}}CSejour-admit{{/tr}}{{else}}{{tr}}Save{{/tr}}{{/if}}
  </button>
  {{if $entree_reelle}}
    <button class="cancel" type="button" onclick="emptyFields();">
      {{tr}}CSejour-cancel_admit{{/tr}}
    </button>
  {{/if}}
</p>
