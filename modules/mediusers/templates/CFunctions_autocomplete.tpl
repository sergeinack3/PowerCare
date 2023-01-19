{{*
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{* Do not add carriage returns or it will add whitespace in the input *}}
<div class="me-autocomplete-functions" style="border-left: 3px solid #{{$match->color}}; padding-left: 2px; margin: -1px;">
  <div style="background-color: #{{$match->color}};"></div>
  <span class="view" {{if $match->actif == 0}}style="text-decoration: line-through;"{{/if}}>{{if $show_view || !$f}}{{$match}}{{else}}{{$match->$f|emphasize:$input}}{{/if}}</span>
</div>
