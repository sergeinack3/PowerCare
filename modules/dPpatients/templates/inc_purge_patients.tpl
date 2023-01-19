{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $suppr}}
  <div class="small-success">{{$suppr}} patients supprim�s</div>
{{/if}}
{{if $error}}
  <div class="small-error">{{$error}} patients non supprim�s</div>
{{/if}}
<div class="small-info">{{$nb_patients}} patients dans la base</div>

<div>
  {{$resultsMsg|smarty:nodefaults}}
</div>
