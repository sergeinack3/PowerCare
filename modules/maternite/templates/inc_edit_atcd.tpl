{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=dossier_medical value=$patient->_ref_dossier_medical}}

<script>
  emptyFields = function () {
    var form = getForm("editAntFrm");

    $V(form.date, "");
    $V(form.date_da, "");
    if (form.type.visible()) {
      $V(form.type, "");
    }
    $V(form.appareil, "");
    $V(form.rques, "");
  };

  easyMode = function () {
    new Url("cabinet", "vw_ant_easymode")
      .addParam("patient_id", "{{$patient->_id}}")
      .pop(900, 600, "Mode grille");
  };

  onSubmitAnt = function (form) {
    return onSubmitFormAjax(form, DossierMater.refreshAtcd('{{$type}}'));
  };

  updateFieldsComposant = function (selected) {
    var composant = selected.down('.view').getText();
    var code_composant = selected.get("code");

    var form_allergie = getForm("addComposant");
    $V(form_allergie.rques, composant);

    $V(form_allergie._idex_code, code_composant);
    $V(form_allergie._idex_tag, "BCB_COMPOSANT");

    form_allergie.onsubmit();
  };

  Main.add(function () {
    if ($("composant_autocomplete")) {
      new Url("medicament", "ajax_composant_autocomplete")
        .autoComplete(getForm("addComposant").keywords_composant, "composant_autocomplete", {
            minChars:      3,
            updateElement: updateFieldsComposant
          }
        );
    }
  });
</script>

<table class="main">
  <tr>
    <td class="halfPane">
      {{if $type == "alle"}}
        <fieldset>
          <legend>Composant</legend>
          <form name="addComposant" method="post" onsubmit="return onSubmitFormAjax(this, function() {
            DossierMater.refreshAtcd('{{$patient->_id}}', '{{$type}}', 1, 'list_{{$type}}');
            })">
            <input type="hidden" name="m" value="patients" />
            <input type="hidden" name="del" value="0" />
            <input type="hidden" name="dosql" value="do_antecedent_aed" />
            <input type="hidden" name="_patient_id" value="{{$patient->_id}}" />
            <input type="hidden" name="type" value="alle" />
            <input type="hidden" name="rques" />

            <input type="text" name="keywords_composant" size="50" class="autocomplete" />
            <div style="display:none; width: 350px;" class="autocomplete" id="composant_autocomplete"></div>
            <input type="hidden" name="_idex_code" />
            <input type="hidden" name="_idex_tag" />
          </form>
        </fieldset>
      {{/if}}

      <fieldset>
        <legend>
          {{tr}}CAntecedent-Free text{{/tr}}
          {{if $type != "alle" && (!"dPpatients CAntecedent create_antecedent_only_prat"|gconf ||
            $app->user_prefs.allowed_to_edit_atcd || $app->_ref_user->isPraticien() || $app->_ref_user->isSageFemme())}}
            <button type="button" class="list" onclick="easyMode()">{{tr}}CDossierPerinat-Grid mode{{/tr}}</button>
          {{/if}}
          {{if "appFineClient"|module_active && "appFineClient Sync allow_appfine_sync"|gconf}}
            {{mb_include module=appFineClient template=inc_show_ant}}
          {{/if}}
        </legend>
        <form name="editAntFrm" method="post" onsubmit="return onSubmitFormAjax(this, function() {
          DossierMater.refreshAtcd('{{$patient->_id}}', '{{$type}}', 1, 'list_{{$type}}');
          emptyFields();
          })">
          <input type="hidden" name="m" value="patients" />
          <input type="hidden" name="dosql" value="do_antecedent_aed" />
          <input type="hidden" name="del" value="0" />
          {{mb_key object=$antecedent}}
          <input type="hidden" name="_patient_id" value="{{$patient->_id}}" />

          <input type="hidden" name="type" value="{{$type}}" />
          <input type="hidden" name="_idex_code" value="" />
          <input type="hidden" name="_idex_tag" value="" />
          <input type="hidden" name="reaction_indesirable" value="" />

          <table class="form">
            <tr>
              {{if $app->user_prefs.showDatesAntecedents}}
                <th>{{mb_label object=$antecedent field=date}}</th>
                <td style="height: 20px">{{mb_field object=$antecedent field=date form="editAntFrm" register=true}}</td>
              {{/if}}
              <td style="width: 60%">
                {{mb_field class=notNull object=$antecedent field=rques form=editAntFrm}}
              </td>
            </tr>
            <tr>
              <td colspan="3" class="button">
                <button type="button" class="tick"
                        onclick="this.form.onsubmit();">{{tr}}CAntecedent-action-Add the antecedent{{/tr}}</button>
              </td>
            </tr>
          </table>
        </form>
      </fieldset>
    </td>
    <td>
      <fieldset>
        <legend>{{if $type == "alle"}}Allergies{{else}}Antécédents {{tr}}CAntecedent.type.{{$type}}{{/tr}}{{/if}}</legend>

        <div id="list_{{$type}}" style="height: 250px;  overflow-y: auto;">
          {{mb_include template=inc_list_antecedents dossier_medical=$dossier_medical antecedents=$dossier_medical->_ref_antecedents_by_type.$type type=$type edit=1}}
        </div>
      </fieldset>
    </td>
  </tr>
</table>