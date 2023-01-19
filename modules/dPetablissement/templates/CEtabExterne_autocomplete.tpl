{{*
 * @package Mediboard\Etablissement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<span class="view" style="float: left;" data-provenance="{{$match->provenance}}" data-destination="{{$match->destination}}">
  {{if $show_view}}{{$match->_view}}{{else}}{{$match->$f|emphasize:$input}}{{/if}}
</span>

<div style="color: #666; font-size: 0.7em; padding-left: 0.5em; clear: both;">
  {{if $match->cp && $match->ville}}{{$match->cp}} {{$match->ville}}{{/if}}&nbsp;
</div>