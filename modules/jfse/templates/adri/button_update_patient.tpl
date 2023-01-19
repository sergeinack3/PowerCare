{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=jfse script=Jfse ajax=$ajax}}
{{mb_script module=jfse script=Adri ajax=$ajax}}

<button type="button" onclick="Adri.updatePatient({{$patient->_id}})" class="hslip me-tertiary singleclick">
    {{tr}}AdriController-Update ADRi{{/tr}}
</button>
