{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module="cabinet" script="icone_selector" ajax=true}}
{{mb_script module="patients" script="patient" ajax=true}}

<script>
  Main.add(function() {
    Consultation.onCloseEditModal = function() {location.reload()};
  });
</script>

<table class="tbl me-margin-top-0">
  {{mb_include module=cabinet template=inc_consultations_lines chirSel=$plageSel->chir_id}}
</table>