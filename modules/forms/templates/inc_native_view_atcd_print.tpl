{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=patient value=$object->loadRelPatient()}}
{{assign var=dossier_medical value=$patient->loadRefDossierMedical()}}
{{assign var=dossier_medical_complete value=$dossier_medical->loadComplete()}}
{{assign var=prescription_sejour value=null}}
{{assign var=LinesMed     value=null}}

{{if $object|instanceof:'Ox\Mediboard\PlanningOp\CSejour'}}
  {{assign var=prescription_sejour value=$object->loadRefPrescriptionSejour()}}
  {{assign var=LinesMed     value=$prescription_sejour->loadRefsLinesMedComments("1", "1", "1", "", "", "0", "1")}}
{{/if}}

{{mb_include module=patients template=CDossierMedical_complete object=$dossier_medical prescription=$prescription_sejour hide_header=true}}