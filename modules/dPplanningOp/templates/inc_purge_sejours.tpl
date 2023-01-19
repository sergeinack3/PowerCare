{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $suppr}}
  <div class="small-success">{{$suppr}} sejours supprimés</div>
{{/if}}
{{if $error}}
  <div class="small-error">{{$error}} sejours non supprimés</div>
{{/if}}
<div class="small-info">{{$nb_sejours}} sejours dans la base</div>

<div class="big-info">
  {{$resultsMsg|smarty:nodefaults}}
</div>