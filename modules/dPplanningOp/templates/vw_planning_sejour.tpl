{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<style>
  @media print {
    @page {
      size: landscape;
    }
    .week-container {
      overflow-y: hidden !important;
    }
  }

</style>

<div id="planning-{{$sejour->_guid}}">
  {{mb_include module=planningOp template=inc_planning_sejour}}
</div>