{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div>
  <span class="view">
    {{if $show_view || !$f}}{{$match->name}}{{else}}{{$match->$f|emphasize:$input}}{{/if}}
  </span>

  {{if $match->_ref_tag_items}}
    <br />

    <ul class="tags" style="display: inline-block; float: right;">
      {{foreach from=$match->_ref_tag_items item=_tag_item}}
        <li class="tag me-tag" style="float: right; background-color: #{{$_tag_item->_ref_tag->color|default:'ccc'}}; font-size: 0.8em;">
          {{$_tag_item->_ref_tag}}
        </li>
      {{/foreach}}
    </ul>
  {{/if}}
</div>