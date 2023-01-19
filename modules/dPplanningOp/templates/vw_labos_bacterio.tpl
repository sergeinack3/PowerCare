{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=planningOp script=labo_bacterio ajax=$ajax}}

<script>
  Main.add(function() {
    LaboBacterio.refreshList();
  });
</script>

<div>
  <button class="new" onclick="LaboBacterio.edit();">{{tr}}CLaboratoireBacterio-title-create{{/tr}}</button>
</div>

<div id="labos_bacterio_area"></div>
