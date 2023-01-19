{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=patients script=IdentityProofType}}
{{mb_script module=dPpatients script=export_patients}}

<div>
    {{mb_include module=patients template=identity_proof_type/filters}}
</div>
<div id="list_identity_proof_types">
    {{mb_include module=patients template=identity_proof_type/list}}
</div>
