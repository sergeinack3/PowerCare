{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=dossier_medical value=$sejour->_ref_dossier_medical}}

{{assign var=create_antecedent_only_prat value=0}}
{{if "dPpatients CAntecedent create_antecedent_only_prat"|gconf && !$app->_ref_user->isPraticien() && !$app->_ref_user->isSageFemme()}}
  {{assign var=create_antecedent_only_prat value=1}}
{{/if}}
{{if !$sejour->_id}}
  <div class="empty">Aucun séjour sélectionné</div>
  {{mb_return}}
{{/if}}

<script>
  var cancelledAnesthVisible = true;

  onSubmitDossierMedical = function(oForm) {
    return onSubmitFormAjax(oForm, {
      onComplete : function() {
        DossierMedical.reloadDossierSejour();
        if (window.reloadAtcd) {
          reloadAtcd();
        }
      }
    } );
  };

  copyAntecedent = function(antecedent_id){
    var oForm = document.frmCopyAntecedent;
    oForm.antecedent_id.value = antecedent_id;
     onSubmitDossierMedical(oForm);
  };

  toggleCancelledAnesth = function(list) {
    $(list).select('.cancelled').each(Element.show);
  }

  Main.add(function() {
    oCimAnesthField = new TokenField(getForm("editDiagAnesthFrm").codes_cim, {
      confirm  : 'Voulez-vous réellement supprimer ce diagnostic ?',
      onChange : updateTokenCim10Anesth
    });
  });
</script>

<form name="frmCopyAntecedent" action="?m=dPcabinet" method="post">
  <input type="hidden" name="m" value="dPpatients" />
  <input type="hidden" name="del" value="0" />
  <input type="hidden" name="dosql" value="do_copy_antecedent" />
  <input type="hidden" name="antecedent_id" value="" />
  <input type="hidden" name="_sejour_id" value="{{$sejour->_id}}" />
</form>

<form name="editDiagAnesthFrm" action="?m=dPcabinet" method="post" onsubmit="return checkForm(this);">
  <input type="hidden" name="del" value="0" />
  <input type="hidden" name="m" value="dPpatients" />
  <input type="hidden" name="tab" value="edit_consultation" />
  <input type="hidden" name="dosql" value="do_dossierMedical_aed" />
  <input type="hidden" name="object_id" value="{{$sejour->_id}}" />
  <input type="hidden" name="object_class" value="CSejour" />
  <input type="hidden" name="codes_cim" value="{{$dossier_medical->codes_cim}}" />
</form>

{{if $dossier_medical->_count_cancelled_antecedents}}
  <button class="search not-printable" style="float: right" onclick="Antecedent.toggleCancelled('antecedents-{{$dossier_medical->_guid}}')">
    Afficher les {{$dossier_medical->_count_cancelled_antecedents}} antécédents annulés
  </button>
{{/if}}

<strong {{if $dossier_medical->_count_cancelled_antecedents}}style="line-height: 22px;"{{/if}}>{{tr}}CAntecedent-significatif|pl{{/tr}}</strong>

<ul id="antecedents-{{$dossier_medical->_guid}}">
  {{if $dossier_medical->_count_antecedents || $dossier_medical->_count_cancelled_antecedents}}
    {{foreach from=$dossier_medical->_ref_antecedents_by_type key=_type item=list_antecedent}}
      {{foreach from=$list_antecedent item=_antecedent}}
        <li {{if $_antecedent->annule}}class="cancelled" style="display: none;"{{/if}}>
          <!-- Seulement si l'utilisateur est le créateur -->
          {{if $_antecedent->owner_id == $app->user_id && !$create_antecedent_only_prat}}
          <form name="Del-{{$_antecedent->_guid}}" action="?m=dPcabinet" method="post" class="not-printable">
            <input type="hidden" name="m" value="dPpatients" />
            <input type="hidden" name="del" value="0" />
            <input type="hidden" name="dosql" value="do_antecedent_aed" />
            {{mb_key object=$_antecedent}}

            <input type="hidden" name="annule" value="" />

            <button title="{{tr}}Delete{{/tr}}" class="trash notext me-tertiary me-dark" type="button" onclick="
                Antecedent.remove(this.form, function() {
                  DossierMedical.reloadDossierSejour();
                  if (window.reloadAtcd) {
                    reloadAtcd();
                  }
                  if (window.reloadAtcdMajeur) {
                    reloadAtcdMajeur();
                  }
                  if (window.reloadAtcdOp) {
                    reloadAtcdOp();
                  }
                })">
              {{tr}}Delete{{/tr}}
            </button>
          </form>
          {{/if}}

          <span {{if $_antecedent->majeur}}style="color: #f00;"{{elseif $_antecedent->important}}style="color: #fd7d26;"{{/if}}
                onmouseover="ObjectTooltip.createEx(this, '{{$_antecedent->_guid}}')">
            <strong style="margin-right: 5px;">
              {{if $_antecedent->type    }} {{mb_value object=$_antecedent field=type    }} {{/if}}
              {{if $_antecedent->appareil}} {{mb_value object=$_antecedent field=appareil}} {{/if}}
            </strong>
            {{if $_antecedent->date}}
              [{{mb_value object=$_antecedent field=date}}] :
            {{/if}}
            {{$_antecedent->rques|nl2br}}{{if $_antecedent->family_link && ($_antecedent->type == "fam")}} ({{mb_value object=$_antecedent field="family_link"}}){{/if}}
          </span>
        </li>
      {{/foreach}}
    {{/foreach}}
  {{else}}
    <li class="empty">{{tr}}CAntecedent.none{{/tr}}</li>
  {{/if}}
</ul>

{{if $dossier_medical->_count_cancelled_traitements}}
  <button class="search not-printable" style="float: right" onclick="Traitement.toggleCancelled('traitements-{{$dossier_medical->_guid}}')">
    Afficher les {{$dossier_medical->_count_cancelled_traitements}} traitements stoppés
  </button>
{{/if}}

{{if is_array($dossier_medical->_ref_traitements)}}
<!-- Traitements -->
  <strong>{{tr}}CTraitement-significatif|pl{{/tr}}</strong>
  <ul id="traitements-{{$dossier_medical->_guid}}">
    {{foreach from=$dossier_medical->_ref_traitements item=curr_trmt}}
    <li {{if $curr_trmt->annule}}class="cancelled" style="display: none;"{{/if}}>
      <form name="delTrmtFrm-{{$curr_trmt->_id}}" action="?m=dPcabinet" method="post">
        <input type="hidden" name="m" value="dPpatients" />
        <input type="hidden" name="del" value="0" />
        <input type="hidden" name="dosql" value="do_traitement_aed" />
        <input type="hidden" name="traitement_id" value="{{$curr_trmt->_id}}" />
        {{if !$create_antecedent_only_prat}}
          <button class="trash notext not-printable me-tertiary me-dark" type="button" onclick="Traitement.remove(this.form, DossierMedical.reloadDossierSejour)">
            {{tr}}delete{{/tr}}
          </button>
        {{/if}}
        {{if $curr_trmt->fin}}
          Depuis {{mb_value object=$curr_trmt field=debut}}
          jusqu'à {{mb_value object=$curr_trmt field=fin}} :
        {{elseif $curr_trmt->debut}}
          Depuis {{mb_value object=$curr_trmt field=debut}} :
        {{/if}}
        <span onmouseover="ObjectTooltip.createEx(this, '{{$curr_trmt->_guid}}')">
          {{$curr_trmt->traitement|nl2br}}
        </span>
      </form>
    </li>
    {{foreachelse}}
    <li class="empty">Pas de traitements</li>
    {{/foreach}}
  </ul>
{{/if}}

<!-- Traitements -->
{{if is_array($lines_tp)}}
  <strong>{{tr}}CPrescriptionLineMedicament-tp|pl{{/tr}}</strong>
  <ul>
    {{foreach from=$lines_tp item=_line}}
    <li>
      {{if $_line->date_arret && $_line->time_arret}}
        <i class="me-icon cross-circle me-error"
           title="Arrêt{{if $_line->arret_urgence}} en urgence{{/if}}: {{$_line->date_arret|date_format:$conf.date}} "></i>
      {{/if}}
      {{if $_line->suspendu}}
        <span class="texticon texticon-lt" title="{{tr}}CPrescriptionLineMedicament-suspendu-desc{{/tr}}">
            {{tr}}CPrescriptionLineMedicament-suspendu{{/tr}}
          </span>
      {{/if}}
      <form name="delTraitementDossierMedPat-{{$_line->_id}}" action="?" method="post">
        <input type="hidden" name="m" value="mpm" />
        <input type="hidden" name="del" value="1" />
        <input type="hidden" name="dosql" value="do_prescription_line_medicament_aed" />
        <input type="hidden" name="prescription_line_medicament_id" value="{{$_line->_id}}" />
        {{if !$_line->signee && !$create_antecedent_only_prat}}
          <button class="trash notext not-printable me-tertiary me-dark" type="button" onclick="Traitement.remove(this.form, DossierMedical.reloadDossierSejour)">
            {{tr}}delete{{/tr}}
          </button>
        {{/if}}

        {{mb_include module=system template=inc_interval_date from=$_line->debut to=$_line->_fin}}
        <span onmouseover="ObjectTooltip.createEx(this, '{{$_line->_guid}}', 'objectView')">
            <a href="#1" onclick="Prescription.viewProduit(null,'{{$_line->code_ucd}}','{{$_line->code_cis}}');">
              {{$_line->_ucd_view}}
            </a>
        </span>

        <span class="compact" style="display: inline;">
          {{$_line->commentaire}}
          {{if $_line->_ref_prises|@count}}
            <br />
            ({{foreach from=`$_line->_ref_prises` item=_prise name=foreach_prise}}
            {{$_prise}}{{if !$smarty.foreach.foreach_prise.last}},{{/if}}
          {{/foreach}})
          {{/if}}
        </span>
      </form>
    </li>
    {{foreachelse}}
    <li class="empty">Pas de traitements personnels</li>
    {{/foreach}}
  </ul>
{{/if}}

<strong>{{tr}}CDossierMedical-diag_significatif|pl{{/tr}}</strong>
<ul class="diagnostics_significatifs">
  {{foreach from=$dossier_medical->_ext_codes_cim item=_code}}
  <li>
    {{if !$create_antecedent_only_prat}}
      <button class="trash notext not-printable me-tertiary me-dark" type="button" onclick="oCimAnesthField.remove('{{$_code->code}}')">
        {{tr}}delete{{/tr}}
      </button>
    {{/if}}
    {{$_code->code}}: {{$_code->libelle}}
  </li>
  {{foreachelse}}
  <li class="empty">Pas de diagnostic</li>
  {{/foreach}}
</ul>
