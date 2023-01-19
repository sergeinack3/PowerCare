{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_include module=system template=calendars/vw_week}}

<script>
  ObjectTooltip.modes.plage_groupe_view = {
    module: "ssr",
    action: "ajax_vw_custom_tooltip_groupe_patient",
    sClass: "tooltip"
  };
</script>
