{{*
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<span class="view" style="float: left;">{{if $show_view}}{{$match->_view}}{{else}}{{$match->$f|emphasize:$input}}{{/if}}</span>

<div style="color: #666; font-size: 0.8em; padding-left: 0.5em; clear: both;">
  <div style="float: right; color: #999; text-align: center;">
    {{$match->loadRefsFwd()}}
    {{$match->_ref_category}}
  </div>

  {{$match->code}}
</div>