{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $tree.fwd|@count == 0 && $tree.back|@count == 0}}
  {{mb_return}}
{{/if}}

<ul {{if $deepness > 0}} style="display: none;" {{else}} class="treeview" {{/if}} >
  {{foreach from=$tree.fwd key=_key item=_data}}
    {{if $path}}
      {{assign var=_path value="$path f:$_key"}}
    {{else}}
      {{assign var=_path value="f:$_key"}}
    {{/if}}

    <li class="backref">
      <label>
        <input type="checkbox" value="{{$_path}}" class="history" />
        {{tr}}{{$_data.class}}-{{$_key}}{{/tr}}
      </label>

      {{if $_data.subtree.fwd|@count > 0 || $_data.subtree.back|@count > 0}}
        {{mb_include module=system template=inc_full_history_tree tree=$_data.subtree deepness=$deepness+1 path=$_path}}
      {{/if}}
    </li>
  {{/foreach}}

  {{foreach from=$tree.back key=_key item=_data}}
    {{if $path}}
      {{assign var=_path value="$path b:$_key"}}
    {{else}}
      {{assign var=_path value="b:$_key"}}
    {{/if}}

    <li class="fwdref">
      <label>
        <input type="checkbox" value="{{$_path}}" class="history" />
        {{tr}}{{$_data.declaring_class}}-back-{{$_key}}{{/tr}}
      </label>

      {{if $_data.subtree.fwd|@count > 0 || $_data.subtree.back|@count > 0}}
        {{mb_include module=system template=inc_full_history_tree tree=$_data.subtree deepness=$deepness+1 path=$_path}}
      {{/if}}
    </li>
  {{/foreach}}
</ul>