{{*
 * @package Mediboard\BloodSalvage
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=bloodSalvage script=bloodSalvage}}

<script>
  Main.add(function () {
    var url = new Url("bloodSalvage", "httpreq_total_time");
    url.addParam("blood_salvage_id", "{{$blood_salvage->_id}}");
    url.periodicalUpdate("totaltime", {frequency: 60});
  });
</script>

{{mb_include module=bloodSalvage template=inc_bs_sspi_header}}

<div id="timing">
  {{mb_include module=bloodSalvage template=inc_vw_bs_sspi_timing}}
</div>
<div id="totaltime">
  {{mb_include module=bloodSalvage template=inc_total_time}}
</div>
<div id="cell-saver-infos">
  {{mb_include module=bloodSalvage template=inc_vw_cell_saver_volumes}}
</div>
<div id="materiel-cr">
  {{mb_include module=bloodSalvage template=inc_vw_blood_salvage_sspi_materiel}}
  {{mb_include module=bloodSalvage template=inc_blood_salvage_cr_fsei}}
</div>
<div id="listNurse">
  {{mb_include module=bloodSalvage template=inc_vw_blood_salvage_personnel}}
</div>