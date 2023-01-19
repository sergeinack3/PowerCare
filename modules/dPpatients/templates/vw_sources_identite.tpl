{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=patients script=source_identite ajax=$ajax}}

<script>
  Main.add(function() {
    SourceIdentite.patient_id = '{{$patient->_id}}';
  });
</script>

<div id="sources_patient_area">
  {{mb_include module=patients template=inc_list_sources_identite}}
</div>

