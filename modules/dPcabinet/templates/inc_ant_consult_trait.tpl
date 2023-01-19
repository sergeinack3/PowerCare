{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=addform value=""}}
{{mb_default var=type_see value=""}}
{{mb_default var=object value=""}}

{{assign var=mod_snomed value="snomed"|module_active}}

{{if $mod_snomed}}
  {{mb_script module=snomed script=snomed register=true}}
{{/if}}

<script>
  {{if $object && $object->_class == 'CConsultation' && $drc}}
    searchDRC = function() {
      var url = new Url('cim10', 'drc');
      url.addParam('consult_id', '{{$object->_id}}');
      url.requestModal(1230, 800, {onClose: function() {
        loadAntTrait();
        loadExams();
      }});
    };
  {{/if}}

  {{if $object && $object->_class == 'CConsultation' && $cisp}}
    searchCISP = function() {
      new Url('cim10', 'cisp')
        .addParam('patient_id', '{{$object->patient_id}}')
        .requestModal(1230, 800, {onClose: function() {
          loadAntTrait();
          loadExams();
        }});
    };
  {{/if}}
</script>

<tr>
  <td>
    {{mb_include module=cabinet template=inc_ant_allergie}}

    {{assign var=traitement_enabled value="dPpatients CTraitement enabled"|gconf}}

    {{if "dPprescription"|module_active || $traitement_enabled}}
      {{mb_include module=cabinet template=inc_traitement}}
    {{/if}}

    <fieldset {{if "dPpatients CAntecedent create_antecedent_only_prat"|gconf && !$app->user_prefs.allowed_to_edit_atcd &&
      !$app->_ref_user->isPraticien() && !$app->_ref_user->isSageFemme()}}style="display:none;"{{/if}} class="me-margin-bottom-12">
      <legend>Base de diagnostic</legend>
      <script>
        Main.add(function () {

          CIM.autocomplete(getForm("addDiagFrm").keywords_code, null, {
            afterUpdateElement: function(input) {
              var form = getForm("addDiagFrm");
              $V(form.code_diag, input.value);

              {{if $mod_snomed}}
                Snomed.checkCodeExist($V(form.code_diag), '{{$patient->_ref_dossier_medical->_guid}}');
              {{/if}}

              reloadCim10($V(form.code_diag));
            }
          });
        });
      </script>
      <form name="addDiagFrm" action="?m=dPcabinet" method="post" onsubmit="return false;">
        <strong>{{tr}}CDossierMedical-action-Add a diagnosis CIM10{{/tr}}</strong>
        <input type="hidden" name="chir" value="{{$userSel->_id}}" />
        <input type="text" name="keywords_code" class="autocomplete str code cim10" value="" size="10" />
        <input type="hidden" name="code_diag" onchange="$V(this.form.keywords_code, this.value)" />
        <button class="search me-tertiary me-dark" type="button" onclick="CIM.viewSearch(function(code) {$V(getForm('addDiagFrm').elements['code_diag'], code); reloadCim10(code);}, '{{$userSel->_id}}');">
          {{tr}}Search{{/tr}}
        </button>
        {{if $drc && $object && $object->_class == 'CConsultation'}}
          <button class="search" type="button" onclick="searchDRC();">DRC</button>
        {{/if}}
        {{if $cisp && $object && $object->_class == 'CConsultation'}}
          <button class="search" type="button" onclick="searchCISP();">CISP</button>
        {{/if}}
        <button class="tick notext" type="button" onclick="reloadCim10(this.form.code_diag.value)">{{tr}}Validate{{/tr}}</button>
      </form>
    </fieldset>

    {{if "maternite"|module_active && $patient->sexe == "f" && $patient->_annees > 12}}
      {{mb_include module=maternite template=inc_fieldset_etat_actuel}}

      {{if $sejour_id}}
        {{mb_include module=maternite template=inc_fieldset_naissances}}
      {{/if}}
    {{/if}}
  </td>
</tr>
