{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=parent value=false}}

<select name="hierarchy_{{$level}}" id="searchCsARR_hierarchy_{{$level}}"{{if !$parent && $level != 1}} disabled{{/if}}{{if $level != 3}} onchange="CsARR.refreshHierarchySelector(this, '{{$level+1}}');"{{/if}} style="width: 250px;">
  <option value="">
    &mdash; {{tr}}Select{{/tr}}
  </option>
  {{if $level == 1}}
    {{foreach from=$chapters item=_chapter}}
      <option value="{{$_chapter->code}}">
        {{$_chapter->libelle|smarty:nodefaults}}
      </option>
    {{/foreach}}
  {{elseif $parent}}
    {{foreach from=$parent->_ref_child_hierarchies item=_hierarchy}}
      <option value="{{$_hierarchy->code}}">
        {{$_hierarchy->libelle|smarty:nodefaults}}
      </option>
    {{/foreach}}
  {{/if}}
</select>