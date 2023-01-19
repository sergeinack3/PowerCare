{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=admissions script=admissions         ajax=true}}
{{mb_script module=planningOp script=sejour             ajax=true}}
{{mb_script module=patients   script=identity_validator ajax=true}}

<script>
  Main.add(() => {
    {{if "dPpatients CPatient manage_identity_vide"|gconf}}
      IdentityValidator.active = true;
    {{/if}}
  });
</script>

{{assign var=patient value=$sejour->_ref_patient}}

<table class="form me-no-box-shadow">
  <tr>
    <th class="halfPane">{{mb_label object=$sejour field=praticien_id}}</th>
    <td>{{mb_include module=mediusers template=inc_vw_mediuser mediuser=`$sejour->_ref_praticien`}}</td>
    <td class="button" rowspan="4" style="vertical-align: middle;">

      <button class="edit not-printable me-tertiary"
              onclick="IdentityValidator.manage('{{$patient->status}}', '{{$patient->_id}}', Admissions.validerEntree.curry('{{$sejour->_id}}', null, DossierMater.refreshEntreeSortie.curry('{{$sejour->_id}}', 'infos_admission')));">
        Admission
      </button>

      <br />

      <button type="button" class="edit not-printable me-tertiary"
              onclick="Sejour.editModal('{{$sejour->_id}}', 0, 0, DossierMater.refreshEntreeSortie.curry('{{$sejour->_id}}', 'infos_admission'))">
        DHE
      </button>
    </td>
  </tr>
  <tr>
    <th>{{mb_label object=$sejour field=entree_prevue}}</th>
    <td>{{mb_value object=$sejour field=entree_prevue}}</td>
  </tr>
  <tr>
    <th>{{mb_label object=$sejour field=entree_reelle}}</th>
    <td>{{mb_value object=$sejour field=entree_reelle}}</td>
  </tr>
  <tr>
    <th>{{mb_label object=$sejour field=mode_entree}}</th>
    <td>{{mb_value object=$sejour field=mode_entree}}</td>
  </tr>
</table>
