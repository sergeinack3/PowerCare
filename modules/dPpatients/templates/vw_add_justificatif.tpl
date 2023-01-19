{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=patients script=documentV2 ajax=$ajax}}

<script>
  Main.add(function() {
    var form = getForm('addJustificatif');

    initPaysField(form.name, '_source__pays_naissance_insee');
    InseeFields.initCPVille(
      form.name,
      '_source_cp_naissance',
      '_source_lieu_naissance',
      '_source__code_insee',
      '_source__pays_naissance_insee',
    );

    InseeFields.initCodeInsee(form.name, '_source__code_insee', '_source_');

    form.elements['formfile[]'].observe('change', () => {
      $$('.existing_file').invoke('hide');
    });
  });
</script>

<form name="addJustificatif" method="get" onsubmit="return Patient.submitJustificatif();">
  <input type="hidden" name="patient_id" value="{{$patient_id}}" />

  <table class="form">
    {{if $patient_id}}
    <tr>
      <td class="button existing_file" colspan="3">
        <button type="button" class="search"
                onclick="DocumentV2.viewDocs('{{$patient_id}}', '{{$patient_id}}', 'CPatient', null, 'SourceIdentite.copyFile(this)', 0, 1, 0);">
          {{tr}}CSourceIdentite-Choose existing file{{/tr}}
        </button>

        <input type="hidden" name="_copy_file_id" onchange="$$('.new_file').invoke('hide');"/>
        <div id="file_name_copy" class="empty me-padding-top-8">
          <span>{{tr}}CFile.none{{/tr}}</span>
        </div>
      </td>
    </tr>
    <tr>
      <td colspan="3" class="button existing_file new_file">
        {{tr}}or{{/tr}}
      </td>
    </tr>
    {{/if}}
    <tr>
      <td colspan="3" class="new_file">
        {{mb_include module=system template=inc_inline_upload lite=true multi=false paste=false extensions="gif jpeg jpg png"}}
      </td>
    </tr>
    <tr>
      <th style="width: 30%;">
        {{mb_label class=CPatient field=_identity_proof_type_id}}
      </th>
      <td colspan="2">
        <select name="_identity_proof_type_id" class="notNull" onchange="SourceIdentite.manageIdentityProofType(this);">
          <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
          {{foreach from=$identity_proof_types item=_identity_proof_type}}
              <option value="{{$_identity_proof_type->_id}}"
                      data-code="{{$_identity_proof_type->code}}"
                      data-trust-level="{{$_identity_proof_type->trust_level}}"
                      data-validate-identity="{{$_identity_proof_type->validate_identity}}">
                  {{$_identity_proof_type->_view}}
              </option>
          {{/foreach}}
        </select>

        {{if $use_id_interpreter}}
          <button type="button" class="fas fa-id-card notext me-tertiary me-dark id-interpreter" style="display: none;"
                  onclick="IdInterpreter.open(this.form, getForm('editFrm'))">
            {{tr}}CIdInterpreter.fill_from_image{{/tr}}
          </button>
        {{/if}}
      </td>
    </tr>
    <tr>
      <th>
        {{mb_label class=CPatient field=_source__date_fin_validite}}
      </th>
      <td colspan="2">
        {{mb_field class=CPatient field=_source__date_fin_validite}}
      </td>
    </tr>
    <tr>
      <th>
          {{mb_label class=CPatient field=_source__complete_traits_stricts typeEnum=checkbox}}
      </th>
      <td colspan="2">
          {{mb_field class=CPatient field=_source__complete_traits_stricts typeEnum=checkbox
               onchange="Patient.toggleTraitsStricts(this);"}}
      </td>
    </tr>
    <tbody id="traits-stricts-area" style="display: none;">
      <tr>
        <th>
          {{mb_label class=CPatient field=_source_nom_jeune_fille}}
        </th>
        <td colspan="2">
          {{mb_field class=CPatient field=_source_nom_jeune_fille}}
        </td>
      </tr>
      <tr>
        <th>
          {{mb_label class=CPatient field=_source_prenom}}
        </th>
        <td colspan="2">
          {{mb_field class=CPatient field=_source_prenom onchange="Patient.copyPrenom(this, true);"}}
        </td>
      </tr>
      <tr>
        <th>
          {{mb_label class=CPatient field=_source_prenoms}}
        </th>
        <td colspan="2">
          {{mb_field class=CPatient field=_source_prenoms onchange="Patient.copyPrenom(this, true);"}}
        </td>
      </tr>
      <tr>
        <th>
          {{mb_label class=CPatient field=_source_naissance}}
        </th>
        <td colspan="2">
          {{mb_field class=CPatient field=_source_naissance}}
        </td>
      </tr>
      <tr>
        <th>
            {{mb_label class=CPatient field=_source_sexe}}
        </th>
        <td colspan="2">
            {{mb_field class=CPatient field=_source_sexe value="" typeEnum=radio}}
        </td>
      </tr>
      <tr>
        <th>
            {{mb_label class=CPatient field=_source_civilite}}
        </th>
        <td colspan="2">
            {{mb_field class=CPatient field=_source_civilite emptyLabel="Choose"}}
        </td>
      </tr>
      <tr>
        <th>
          {{mb_label class=CPatient field=_source_cp_naissance}}
        </th>
        <td>
          {{mb_field class=CPatient field=_source_cp_naissance}}
          <div style="display: none;" class="autocomplete" id="_source_cp_naissance_insee_auto_complete"></div>
        </td>
        <td rowspan="3" class="me-valign-top">
          <div class="small-info" style="width: 365px;">
            {{tr}}CSourceIdentite-Information about birth location{{/tr}}
          </div>
        </td>
      </tr>
      <tr>
        <th>
          {{mb_label class=CPatient field=_source_lieu_naissance}}
        </th>
        <td colspan="2">
          {{mb_field class=CPatient field=_source_lieu_naissance}}
          {{mb_field class=CPatient field=_source_commune_naissance_insee hidden=true}}
          <div style="display: none;" class="autocomplete" id="_source_lieu_naissance_insee_auto_complete"></div>
        </td>
      </tr>
      <tr>
        <th>
          {{mb_label class=CPatient field=_source__pays_naissance_insee}}
        </th>
        <td colspan="2">
          {{mb_field class=CPatient field=_source__pays_naissance_insee}}
          <div style="display: none;" class="autocomplete" id="_source__pays_naissance_insee_auto_complete"></div>
        </td>
      </tr>
      <tr>
          <th>
              {{mb_label class=CPatient field=_source__code_insee}}
          </th>
          <td colspan="2">
              {{mb_field class=CPatient field=_source__code_insee}}
              <div style="display: none;" class="autocomplete" id="_source__pays_naissance_insee_auto_complete"></div>
          </td>
      </tr>
    </tbody>

    <tr style="display: none;">
      <th>
          {{mb_label class=CPatient field=_source__validate_identity typeEnum=checkbox}}
      </th>
      <td colspan="2">
          {{mb_field class=CPatient field=_source__validate_identity typeEnum=checkbox}}
      </td>
    </tr>

    <tr>
      <td class="button" colspan="3">
        {{if $patient_id}}
          <button class="save me-primary">{{tr}}Save{{/tr}}</button>
        {{else}}
          <button class="import me-primary">{{tr}}CIdInterpreter.report_data{{/tr}}</button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>
