{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  // UpdateFields de l'autocomplete de medicaments
  updateFieldsMedicamentTP{{$addform}} = function(selected) {
    var oFormTP = getForm("editLineTP{{$addform}}");
    // Submit du formulaire avant de faire le selection d'un nouveau produit
    if ($V(oFormTP._code)) {
      onSubmitFormAjax(oFormTP, function() {
        updateTP{{$addform}}(selected);
        DossierMedical.reloadDossiersMedicaux();
      });
    }
    else {
      updateTP{{$addform}}(selected);
    }
  };

  updateTP{{$addform}} = function(selected) {
    var oFormTP = getForm("editLineTP{{$addform}}");
    resetEditLineTP{{$addform}}();
    Element.cleanWhitespace(selected);
    var dn = selected.childElements();
    dn = dn[0].innerHTML;

    // On peut saisir un traitement personnel seulement le code CIP est valide
    if (isNaN(parseInt(dn))) {
      return
    }
    $V(oFormTP._code, dn);
    $("_libelle{{$addform}}").insert("<button type='button' class='cancel notext' onclick='resetEditLineTP{{$addform}}(); resetFormTP{{$addform}}();'></button>" +
    "<a href=\"#nothing\" onclick=\"Prescription.viewProduit('','','"+selected.down(".code-cis").getText()+"')\">"+
    selected.down(".libelle").getText()+"</a>");

    if (selected.down(".alias")) {
      $("_libelle{{$addform}}").insert(selected.down(".alias").getText());
    }

    if (selected.down(".forme")) {
      $("_libelle{{$addform}}").insert("<br /><span class='compact'>"+selected.down(".forme").getText()+"</span>");
    }

    $V(oFormTP.produit, '');
    $('button_submit_traitement{{$addform}}').focus();
  };

  resetEditLineTP{{$addform}} = function() {
    var oFormTP = getForm("editLineTP{{$addform}}");
    $("_libelle{{$addform}}").update("");
    oFormTP._code.value = '';
  };

  resetFormTP{{$addform}} = function() {
    var oFormTP = getForm("editLineTP{{$addform}}");
    if (oFormTP.conditionnel) {
      oFormTP.__conditionnel.checked = false;
      $V(oFormTP.conditionnel, 0);
    }
    $V(oFormTP.commentaire, '');
    $V(oFormTP.token_poso, '');
    $('addPosoLine{{$addform}}').update('');

    $V(oFormTP.long_cours, 1);
    $V(oFormTP.__long_cours, true);

    if (Preferences.empty_form_atcd == "1") {
      $V(oFormTP.debut, "");
      $V(oFormTP.debut_da, "");
      $V(oFormTP.fin, "");
      $V(oFormTP.fin_da, "");
    }
  };

  refreshAddPoso{{$addform}} = function(code){
    var url = new Url("dPprescription", "httpreq_vw_select_poso");
    url.addParam("_code", code);
    url.addParam("addform", "{{$addform}}");
    url.requestUpdate("addPosoLine{{$addform}}");
  };

  submitAndCallback = function(form, callback) {
    $V(form.callback, callback);
    onSubmitFormAjax(form, function() {
      resetEditLineTP{{$addform}}();
      resetFormTP{{$addform}}();
    });
  };

  checkPosos = function() {
    var div = $("list_posogestion_tp");
    if (div.select("button").length == 0) {
      alert($T('CPrisePosologie-_poso_missing'));
      return false;
    }
    return true;
  };

  Main.add(function() {
    getForm('editLineTP{{$addform}}').produit.focus({preventScroll: true});

    // Autocomplete des medicaments
    var urlAuto = new Url("medicament", "httpreq_do_medicament_autocomplete");
    urlAuto.autoComplete(getForm('editLineTP{{$addform}}').produit, "_produit_auto_complete{{$addform}}", {
      minChars: 3,
      width: '500px',
      updateElement: updateFieldsMedicamentTP{{$addform}},
      callback: function(input, queryString) {
        var form = getForm('editLineTP{{$addform}}');
        return (queryString + "&produit_max=40&only_prescriptible_sf=0&only_prescriptible_inf=0&with_alias=1&mask_generique="+($V(form.mask_generique)?'1':'0'));
      }
    } );
  });
</script>

<!-- Formulaire d'ajout de traitements -->
<form name="editLineTP{{$addform}}" action="?m=cabinet" method="post">
  <input type="hidden" name="m" value="mpm" />
  <input type="hidden" name="dosql" value="addLineTp" />
  <input type="hidden" name="_code" value="" onchange="refreshAddPoso{{$addform}}(this.value);"/>
  <input type="hidden" name="_patient_id" value="{{$patient->_id}}" />
  <input type="hidden" name="praticien_id" value="{{$userSel->_id}}" />
  <input type="hidden" name="callback" value="" />
  <table class="layout">
    <col style="width: 70px;" />
    <col class="narrow" />

    <tr>
      {{me_form_field nb_cells=2 label="common-search" class="me-padding-top-8" }}
        <input type="text" name="produit" size="30" class="autocomplete" />
        <div style="display:none; width: 350px;" class="autocomplete" id="_produit_auto_complete{{$addform}}"></div>
        <button type="button" class="search notext me-tertiary me-dark" onclick="MedSelector.init('produit');"></button>
        <script>
          MedSelector.init = function(onglet) {
            this.sForm = "editLineTP{{$addform}}";
            this.sView = "produit";
            this.sCode = "_code";
            this.sSearch = document.editLineTP{{$addform}}.produit.value;
            this.sSearchByCIS = 1;
            this.selfClose = true;
            this._DCI = 0;
            this.sOnglet = onglet;
            this.traitement_perso = true;
            this.only_prescriptible_sf = 0;
            this.addForm = '{{$addform}}';
            this.pop();
          }
        </script>
      {{/me_form_field}}
      <td class="me-padding-top-8">
        <strong><div id="_libelle{{$addform}}"></div></strong>
      </td>
    </tr>
    <tr>
      <td class="me-no-display"></td>
      <td colspan="2" class="me-padding-top-4 me-padding-bottom-4">
        <input name="mask_generique" value="{{$app->user_prefs.check_default_generique}}" title="Masquer les génériques"
               {{if $app->user_prefs.check_default_generique}}checked{{/if}}
               type="{{if "dPprescription general see_generique"|gconf}}checkbox{{else}}hidden{{/if}}"/>
        {{if "dPprescription general see_generique"|gconf}}
          <label for="mask_generique">Masquer les génériques</label>
        {{/if}}
      </td>
    </tr>
    <tr>
      {{if $app->user_prefs.showDatesAntecedents}}
        {{me_form_field nb_cells=2 mb_object=$line mb_field="debut" class="me-padding-top-8"}}
          {{mb_field object=$line field="debut" register=true form=editLineTP$addform}}
        {{/me_form_field}}
      {{else}}
        <td colspan="2"></td>
      {{/if}}
      <td rowspan="3" id="addPosoLine{{$addform}}"></td>
    </tr>

    {{if $app->user_prefs.showDatesAntecedents}}
      <tr>
        {{me_form_field nb_cells=2 mb_object=$line mb_field="fin" class="me-padding-top-8"}}
          {{mb_field object=$line field="fin" register=true form=editLineTP$addform}}
        {{/me_form_field}}
      </tr>
    {{/if}}

    <tr>
      {{me_form_field nb_cells=2 mb_object=$line mb_field="commentaire" class="me-padding-top-8"}}
        {{mb_field object=$line field="commentaire" size=20 form=editLineTP$addform}}
      {{/me_form_field}}
    </tr>

    <tr>
      {{me_form_bool nb_cells=2 mb_object=$line mb_field="long_cours"}}
        {{mb_field object=$line field="long_cours" typeEnum=checkbox value=1}}
      {{/me_form_bool}}
    </tr>
    <tr>
      {{me_form_bool nb_cells=2 mb_object=$line mb_field="conditionnel"}}
        {{mb_field object=$line field=conditionnel typeEnum=checkbox checked=false}}
      {{/me_form_bool}}
    </tr>

    <tr>
      <td colspan="3" {{if !$gestion_tp}}class="button"{{/if}}>
        <button id="button_submit_traitement{{$addform}}" class="tick me-primary" type="button"
                {{if !$patient->canEdit()}}disabled{{/if}}
                onclick="if (!addToTokenPoso{{$addform}}(0)) { return; } onSubmitFormAjax(this.form, function() {
        {{if $callback}}
          {{$callback}}();
        {{elseif $reload}}
          DossierMedical.reloadDossierPatient('{{$reload}}', '{{$type_see}}');
        {{else}}
          DossierMedical.reloadDossiersMedicaux();
        {{/if}}
          resetEditLineTP{{$addform}}();
          resetFormTP{{$addform}}();
          } ); this.form.produit.focus();">
          {{tr}}CPrescriptionLineMedicament-action-add-traitement{{/tr}}
        </button>
        {{if $gestion_tp}}
          <fieldset style="display: inline-block" class="me-no-box-shadow">
            <legend>{{tr}}common-action-Add{{/tr}} {{tr}}and{{/tr}}... <button type="button" class="search notext me-tertiary me-dark" onclick="modal('legend_actions_tp')">{{tr}}Legend{{/tr}}</button></legend>
            <button type="button" class="stop me-tertiary" onclick="addToTokenPoso{{$addform}}(0);submitAndCallback(this.form, 'stopLineTP');">
              {{tr}}Arreter{{/tr}}
            </button>
            <button type="button" class="edit me-tertiary" onclick="addToTokenPoso{{$addform}}(0);submitAndCallback(this.form, 'modifyLineTP');">
              {{tr}}Represcription.edit{{/tr}}
            </button>
            {{if $sejour_id}}
              <button type="button" class="right me-tertiary" onclick="addToTokenPoso{{$addform}}(0); if (checkPosos()) { submitAndCallback(this.form, 'poursuivreLineTP'); }">
                {{tr}}Poursuivre{{/tr}}
              </button>
              <button type="button" class="hslip me-tertiary" onclick="addToTokenPoso{{$addform}}(0);submitAndCallback(this.form, 'relaiLineDialog');">
                {{tr}}Relai{{/tr}}
              </button>
              <button type="button" class="pause me-tertiary" onclick="addToTokenPoso{{$addform}}(0);submitAndCallback(this.form, 'pauseLineDialog')">
                {{tr}}Pause{{/tr}}
              </button>
              <a href="#1" class="button fa fa-stop me-tertiary" onclick="addToTokenPoso{{$addform}}(0);submitAndCallback(getForm('editLineTP{{$addform}}'), 'suspendLineDialog')">
                  {{tr}}Suspendre{{/tr}}
              </a>
            {{/if}}
          </fieldset>
        {{/if}}
      </td>
    </tr>
  </table>
</form>
