{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var="letters" value="A"|range:"Z"}}

<div class="pagination {{if @$narrow}}narrow{{/if}}" style="min-height: 1em; white-space: nowrap;">
  <a href="#1" onclick="{{$change_page}}(''); $(this).addUniqueClassName('active'); return false;" class="page {{if $current == "" || $current == "%"}}active{{/if}}">{{tr}}All{{/tr}}</a>
  {{foreach from=$letters item=letter}}
    <a href="#1" onclick="{{$change_page}}('{{$letter}}'); $(this).addUniqueClassName('active'); return false;" class="page {{if $current == $letter}}active{{/if}}">{{$letter}}</a>
  {{/foreach}}
  <a href="#1" onclick="{{$change_page}}('#'); $(this).addUniqueClassName('active'); return false;" class="page {{if $current == "#"}}active{{/if}}">#</a>
</div>