{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=readonly value=0}}
{{mb_ternary var=view test=$acte->_id value="-"|cat:$acte->_id other="-"|cat:$object->_guid}}
{{mb_default var=code value=""}}
{{mb_default var=target value='listActesNGAP'}}
{{mb_default var=display value=null}}

{{if !$readonly && $acte->_billed && $display != 'pmsi'}}
  {{assign var=readonly value=true}}
{{/if}}

<script>
  Main.add(function() {
    var form = getForm('editActeNGAP-executant_id{{$view}}');
    var url = new Url('mediusers', 'ajax_users_autocomplete');
    url.addParam('edit', '1');
    url.addParam('prof_sante', '1');
    url.addParam('input_field', '_executant_view');
    url.autoComplete(form._executant_view, null, {
      minChars: 0,
      method: 'get',
      select: 'view',
      dropdown: true,
      afterUpdateElement: function(field, selected) {
        var form = getForm('editActeNGAP-executant_id{{$view}}');
        $V(form._executant_view, selected.down('.view').innerHTML);
        $V(form.executant_id, selected.getAttribute('id').split('-')[2]);
        $V(getForm('editActeNGAP{{$view}}')._executant_spec_cpam, selected.down('.view').getAttribute('data-spec_cpam'));
      }
    });

    {{if ($object->_class == 'CConsultation' && $object->sejour_id) || $object->_class == 'CSejour'}}
      url = new Url('mediusers', 'ajax_users_autocomplete');
      url.addParam('edit', '1');
      url.addParam('prof_sante', '1');
      url.addParam('input_field', '_prescripteur_view');
      url.autoComplete(form._prescripteur_view, null, {
        minChars: 0,
        method: 'get',
        select: 'view',
        dropdown: true,
        afterUpdateElement: function(field, selected) {
          var form = getForm('editActeNGAP-executant_id{{$view}}');
          $V(form._prescripteur_view, selected.down('.view').innerHTML);
          $V(form.prescripteur_id, selected.getAttribute('id').split('-')[2]);
        }
      });
    {{/if}}

    {{if !$acte->_id}}
      form = getForm('editActeNGAP-code{{$view}}');
      url = new Url("ccam", "ngapCodeAutocomplete");
      url.addParam("object_id", "{{$object->_id}}");
      url.addParam("object_class", "{{$object->_class}}");

      {{if ($object->_class === 'CSejour' && $object->type == 'urg') || ($object->_class === 'CConsultation' && $object->sejour_id)}}
          url.addParam('urgences', 1);
      {{/if}}

      url.autoComplete(form.code, 'code_ngap_auto_complete{{$view}}', {
        minChars: 1,
        updateElement: function(selected) {
          var form = getForm('editActeNGAP-code{{$view}}');
          $V(form.code, selected.down('.code').innerHTML, false);
          ActesNGAP.syncCodageField(form.code, '{{$view}}');
          ActesNGAP.refreshTarif('{{$view}}');
        },
        callback: function(input, queryString) {
          var form = getForm('editActeNGAP{{$view}}');
          var executant_id = $V(form.executant_id);
          var execution = $V(form.execution).substr(0, 10);
          return queryString + "&executant_id=" + executant_id + '&date=' + execution;
        }
      });

      {{if $code}}
        $V(form.code, '{{$code}}');
        {{if $coefficient}}
          $V(getForm('editActeNGAP-coefficient{{$view}}').coefficient, '{{$coefficient}}');
        {{/if}}
        ActesNGAP.refreshTarif('{{$view}}');
      {{/if}}
    {{/if}}

    {{foreach from=$acte->_forbidden_complements item=_complement}}
      var options = $$('form[name="editActeNGAP-complement{{$view}}"] select[name="complement"] option[value="{{$_complement}}"]');
      options.each(function(option) {
        option.writeAttribute('disabled', 'disabled');
      });
    {{/foreach}}

    {{assign var=date_min value=false}}
    {{assign var=date_max value=false}}
    {{if $object->_class == 'CSejour'}}
      {{assign var=date_min value=$object->entree}}
      {{assign var=date_max value=$object->sortie}}
    {{elseif $object->_class == 'COperation'}}
      {{assign var=date_min value=$object->date}}
      {{assign var=date_max value=$object->date}}
    {{else}}
      {{assign var=date_min value='Ox\Core\CMbDT::date'|static_call:'-27 months':$object->_date}}
      {{if $object->_ref_patient->naissance > $date_min}}
        {{assign var=date_min value=$object->_ref_patient->naissance}}
      {{/if}}
    {{/if}}
    var form_ngap = getForm('editActeNGAP-execution{{$view}}');
    {{if $date_min}}
      Calendar.regField(form_ngap.elements['execution'], {limit: {start: '{{$date_min}}'{{if $date_max}}, stop: '{{$date_max}}'{{/if}} }});
    {{/if}}
    if ($V(form_ngap.execution) == "now") {
      $V(form_ngap.execution_da, 'Maintenant');
    }
  });
</script>

<tr {{if $acte->_id}}class="acteNGAP-line" data-view="{{$view}}"{{/if}}>
  <td>
    {{if $readonly}}
      {{mb_value object=$acte field=quantite}}
    {{else}}
      <form name="editActeNGAP-quantite{{$view}}" action="?" method="post" onsubmit="return false;">
          {{mb_field object=$acte field=quantite onchange="ActesNGAP.setCoefficient(this, '$view');" size=2}}
      </form>
    {{/if}}
  </td>
  <td>
    {{if $readonly}}
      {{mb_value object=$acte field=code}}
    {{else}}
      {{if $acte->_id}}
        <span title="{{$acte->_libelle}}"{{if $acte->lettre_cle}} style="font-weight: bold;"{{/if}}>
          {{mb_value object=$acte field=code}}
        </span>
      {{else}}
        <form name="editActeNGAP-code{{$view}}" action="?" method="post" onsubmit="return false;" class="me-pos-relative">
          {{mb_field object=$acte field=code size=2 onchange="ActesNGAP.syncCodageField(this, '$view');"}}
          <div style="display: none; width: 300px;" class="autocomplete" id="code_ngap_auto_complete{{$view}}"></div>
        </form>
      {{/if}}
    {{/if}}
  </td>
  <td>
    {{if $readonly}}
      {{mb_value object=$acte field=coefficient}}
    {{else}}
      <form name="editActeNGAP-coefficient{{$view}}" action="?" method="post" onsubmit="return false;">
        {{mb_field object=$acte field=coefficient onchange="ActesNGAP.setCoefficient(this, '$view');" size=2}}
      </form>
    {{/if}}
  </td>
  <td>
    {{if $readonly}}
      {{mb_value object=$acte field=demi}}
    {{else}}
      <form name="editActeNGAP-demi{{$view}}" action="?" method="post" onsubmit="return false;">
        {{mb_field object=$acte field=demi onchange="ActesNGAP.syncCodageField(this, '$view');"}}
      </form>
    {{/if}}
  </td>
  <td id="tarifActe{{$view}}">
    {{if $readonly}}
      {{mb_value object=$acte field=montant_base}}
    {{else}}
      <form name="editActeNGAP-montant_base{{$view}}" action="?" method="post" onsubmit="return false;">
        {{if $acte->_id}}
          {{mb_field object=$acte field=montant_base onchange="ActesNGAP.syncCodageField(this, '$view');" size=3 disabled=true}}
        {{else}}
          {{mb_field object=$acte field=montant_base onchange="ActesNGAP.syncCodageField(this, '$view');" size=3}}
        {{/if}}
        {{mb_field object=$acte field=lettre_cle onchange="ActesNGAP.syncCodageField(this, '$view');" hidden=true}}

        {{* Affichage du taux d'abattement pour les indemnités kilométriques des infirmiers *}}
        {{if $acte->isIKInfirmier()}}
          <select name="taux_abattement" onchange="ActesNGAP.changeTauxAbattement(this, '{{$view}}');" style="margin-left: 10px;">
            <option value="1.00"{{if $acte->taux_abattement == 1}} selected="selected"{{/if}}>0%</option>
            <option value="0.50"{{if $acte->taux_abattement == 0.5}} selected="selected"{{/if}}>50%</option>
            <option value="0"{{if $acte->taux_abattement == 0}} selected="selected"{{/if}}>100%</option>
          </select>
          <i class="fa fa-info-circle" style="color: blue;" title="{{tr}}CActeNGAP-msg-taux_abattement{{/tr}}"></i>
        {{/if}}
      </form>
    {{/if}}
  </td>
  <td>
    {{if $readonly}}
      {{mb_value object=$acte field=montant_depassement}}
    {{else}}
      <form name="editActeNGAP-montant_depassement{{$view}}" action="?" method="post" onsubmit="return false;">
        {{mb_field object=$acte field=montant_depassement onchange="ActesNGAP.syncCodageField(this, '$view');" size=3}}
      </form>
    {{/if}}
  </td>
  <td>
    {{if $readonly}}
      {{mb_value object=$acte field=complement}}
    {{else}}
      <form name="editActeNGAP-complement{{$view}}" action="?" method="post" onsubmit="return false;">
        {{mb_field object=$acte field=complement emptyLabel="None" onchange="ActesNGAP.syncCodageField(this, '$view');"}}
      </form>
    {{/if}}
  </td>
  <td>
    {{if $readonly}}
      {{mb_value object=$acte field=gratuit}}
    {{else}}
      <form name="editActeNGAP-gratuit{{$view}}" action="?" method="post" onsubmit="return false;">
        {{mb_field object=$acte field=gratuit typeEnum="select" onchange="ActesNGAP.syncCodageField(this, '$view');"}}
      </form>
    {{/if}}
  </td>
  {{if $object->_class == "CConsultation" || $object->_class == 'CModelCodage'}}
    <td>
      {{if $readonly}}
        {{mb_value object=$acte field=lieu}}
      {{else}}
        <form name="editActeNGAP-lieu{{$view}}" action="?" method="post" onsubmit="return false;">
          {{mb_field object=$acte field=lieu onchange="ActesNGAP.syncCodageField(this, '$view');"}}
        </form>
      {{/if}}
    </td>
  {{/if}}
  <td>
    {{if $readonly}}
      {{mb_value object=$acte field=qualif_depense}}
    {{else}}
      <form name="editActeNGAP-qualif_depense{{$view}}" action="?" method="post" onsubmit="return false;">
        {{mb_field object=$acte field=qualif_depense emptyLabel="Select" onchange="ActesNGAP.syncCodageField(this, '$view');" style="max-width: 100px;"}}
      </form>
    {{/if}}
  </td>
  {{if $object->_ref_patient->ald || ($object->_class == 'CConsultation' && $object->concerne_ALD)}}
    <td>
      {{if $readonly}}
        {{mb_value object=$acte field=ald}}
      {{else}}
        <form name="editActeNGAP-ald{{$view}}" action="?" method="post" onsubmit="return false;">
          {{mb_field object=$acte field=ald onchange="ActesNGAP.syncCodageField(this, '$view');"}}
        </form>
      {{/if}}
    </td>
  {{/if}}
  <td>
    {{if $readonly}}
      {{mb_value object=$acte field=exoneration}}
    {{else}}
      <form name="editActeNGAP-exoneration{{$view}}" action="?" method="post" onsubmit="return false;">
        {{mb_field object=$acte field=exoneration onchange="ActesNGAP.syncCodageField(this, '$view');" style="max-width: 100px;"}}
      </form>
    {{/if}}
  </td>
  <td>
    <i id="info_dep{{$view}}" class="fa fa-lg fa-exclamation-circle" style="{{if !$acte->_dep}}display: none; {{/if}}color: #{{if $acte->accord_prealable && $acte->date_demande_accord && $acte->reponse_accord}}197837{{else}}ffa30c{{/if}};" title="{{tr}}CActeNGAP-msg-dep{{/tr}}"></i>
    <button id="button_edit_dep{{$view}}" type="button" class="edit notext"
            onclick="ActesNGAP.editDEP('{{$acte->_id}}', '{{$view}}', '{{$readonly}}');"
            {{if !$acte->_dep}}style="display: none;"{{/if}}>
        {{tr}}CActeNGAP-accord_prealable{{/tr}}
    </button>
  </td>
  {{if $_is_dentiste}}
    <td>
      {{if $readonly}}
        {{mb_value object=$acte field=numero_dent}}
      {{else}}
        <form name="editActeNGAP-numero_dent{{$view}}" action="?" method="post" onsubmit="return false;">
          {{mb_field object=$acte field=numero_dent onchange="ActesNGAP.checkNumTooth(this, '$view');"}}
        </form>
      {{/if}}
    </td>
  {{/if}}
  <td>
    {{if $readonly}}
      {{mb_value object=$acte field=execution}}
    {{else}}
      <form name="editActeNGAP-execution{{$view}}" action="?" method="post" onsubmit="return false;">
        {{mb_field object=$acte field=execution form="editActeNGAP-execution$view" onchange="ActesNGAP.syncCodageField(this, '$view');" register=true}}
      </form>
    {{/if}}
  </td>
  <td>
    {{if $readonly}}
      {{if ($object->_class == 'CConsultation' && $object->sejour_id) || $object->_class == 'CSejour'}}
        {{mb_label object=$acte field=executant_id}} :
      {{/if}}
      {{mb_value object=$acte field=executant_id}}
      {{if ($object->_class == 'CConsultation' && $object->sejour_id) || $object->_class == 'CSejour'}}
        <br/>
        {{mb_label object=$acte field=prescripteur_id}} :
        {{mb_value object=$acte field=prescripteur_id}}
      {{/if}}
    {{else}}
      <form name="editActeNGAP-executant_id{{$view}}" action="?" method="post" onsubmit="return false;">
        {{if ($object->_class == 'CConsultation' && $object->sejour_id) || $object->_class == 'CSejour'}}
          {{mb_label object=$acte field=executant_id}} :
        {{/if}}

        {{mb_field object=$acte field=executant_id onchange="ActesNGAP.syncCodageField(this, '$view');" hidden=true}}
        <input type="text" name="_executant_view" class="autocomplete" value="{{if $acte->_ref_executant}}{{$acte->_ref_executant}}{{/if}}"/>

        {{if ($object->_class == 'CConsultation' && $object->sejour_id) || $object->_class == 'CSejour'}}
          <br/>
          {{mb_label object=$acte field=prescripteur_id}} :
          {{mb_field object=$acte field=prescripteur_id onchange="ActesNGAP.syncCodageField(this, '$view');" hidden=true}}
          <input type="text" name="_prescripteur_view" class="autocomplete" value="{{if $acte->_ref_prescripteur}}{{$acte->_ref_prescripteur}}{{/if}}"/>
          <button type="button" class="cancel notext me-tertiary me-dark" onclick="$V(this.form.elements['prescripteur_id'], ''); $V(this.form.elements['_prescripteur_view'], '');">{{tr}}Empty{{/tr}}</button>
        {{/if}}
      </form>
    {{/if}}
  </td>

  {{if !$readonly}}
    <td class="narrow">
      <form name="editActeNGAP{{$view}}" action="?" method="post" onsubmit="return ActesNGAP.submit(this, '{{$target}}');">
        {{mb_class object=$acte}}
        {{mb_key object=$acte}}
        <input type="hidden" name="m" value="cabinet">
        <input type="hidden" name="dosql" value="do_acte_ngap_aed">

        {{mb_ternary var=onchange test=$acte->_id value="this.form.onsubmit();" other=""}}

        {{mb_field object=$acte field=object_id hidden=true}}
        {{mb_field object=$acte field=object_class hidden=true}}
        {{mb_field object=$acte field=quantite hidden=true onchange="ActesNGAP.refreshTarif('$view');"}}
        {{mb_field object=$acte field=coefficient hidden=true onchange="ActesNGAP.refreshTarif('$view');"}}
        {{mb_field object=$acte field=taux_abattement hidden=true onchange="ActesNGAP.refreshTarif('$view');"}}
        {{mb_field object=$acte field=demi hidden=true onchange="ActesNGAP.refreshTarif('$view');"}}
        {{mb_field object=$acte field=complement hidden=true onchange="ActesNGAP.refreshTarif('$view');"}}
        {{mb_field object=$acte field=lieu hidden=true onchange=$onchange}}
        {{mb_field object=$acte field=exoneration hidden=true onchange=$onchange}}
        {{mb_field object=$acte field=ald hidden=true onchange=$onchange}}
        {{mb_field object=$acte field=numero_dent hidden=true onchange=$onchange}}
        {{mb_field object=$acte field=executant_id hidden=true onchange=$onchange}}
        {{mb_field object=$acte field=prescripteur_id hidden=true onchange=$onchange}}
        {{mb_field object=$acte field=qualif_depense hidden=true onchange=$onchange}}
        {{mb_field object=$acte field=accord_prealable hidden=true onchange=$onchange}}
        {{mb_field object=$acte field=date_demande_accord hidden=true onchange=$onchange}}
        {{mb_field object=$acte field=reponse_accord hidden=true onchange=$onchange}}
        {{mb_field object=$acte field=montant_depassement hidden=true onchange=$onchange}}
        {{mb_field object=$acte field=execution hidden=true onchange=$onchange}}
        {{mb_field object=$acte field=gratuit hidden=true onchange="ActesNGAP.refreshTarif('$view');"}}
        {{mb_field object=$acte field=comment_acte hidden=true }}

        {{if !$acte->_id}}
          {{mb_field object=$acte field=code hidden=true}}
          {{mb_field object=$acte field=lettre_cle hidden=true}}
          {{mb_field object=$acte field=montant_base hidden=true}}

          <input type="hidden" name="_executant_spec_cpam" value="{{if $acte->_ref_executant}}{{$acte->_ref_executant->spec_cpam_id}}{{/if}}"/>

          <button type="button" class="comment notext" title="{{tr}}CActeNGAP-add-comment-desc{{/tr}}" onclick="ActesNGAP.addComment('editActeNGAP{{$view}}')"></button>
          <button type="button" id="inc_codage_ngap_button_create" class="add notext"
                  onclick="this.form.onsubmit();">{{tr}}Add{{/tr}}</button>
        {{else}}
          {{mb_field object=$acte field=code hidden=true disabled=true}}
          {{mb_field object=$acte field=lettre_cle hidden=true disabled=true}}
          {{mb_field object=$acte field=montant_base hidden=true onchange=$onchange}}
          <input type="hidden" name="del" value="0">

          {{if $object->_class == 'CSejour'}}
            <button class="copy notext" onclick="ActesNGAP.duplicate('{{$acte->_guid}}', '{{$target}}');" type="button">Dupliquer l'acte</button>
          {{/if}}
          <button type="button" class="comment notext" title="{{tr}}CActeNGAP-edit-comment-desc{{/tr}}" onclick="ActesNGAP.editComment('{{$acte->_id}}', '{{$target}}')"></button>
          {{if $object|instanceof:'Ox\Mediboard\Cabinet\CConsultation' && "oxCabinet"|module_active}}
            <button type="button" class="edit notext" onclick="ActesNGAP.edit('{{$acte->_id}}', '{{$target}}');">{{tr}}Edit{{/tr}}</button>
          {{/if}}
          <button type="button" class="remove notext" onclick="ActesNGAP.remove(this.form);">{{tr}}Delete{{/tr}}</button>

        {{/if}}


      </form>
    </td>
  {{/if}}
  <td class="narrow">
    {{if $acte->_id}}
      {{mb_include module=system template=inc_object_history object=$acte}}
    {{/if}}
  </td>
</tr>
