{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=planningOp script=preselector_ufs ajax=true}}

<script>
  Main.add(function() {
    PreselectorUfs.form = $('didac_ufs_button_sejour_protocole').form;
  });
</script>

<button id="didac_ufs_button_sejour_protocole" type="button" class="new me-tertiary" onclick="PreselectorUfs.listingPreselectionUfs();">
  UFs
</button>
