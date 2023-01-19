{{*
 * @package Mediboard\Ccam
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=show_empty value=false}}
{{mb_default var=tag_id value=0}}

{{foreach from=$tag_tree.children item=_subtree}}
  {{if $_subtree.objects|@count > 0 || $show_empty || $_subtree.children|@count > 0}}
    <option value="{{$_subtree.parent->tag_id}}"
            style="background-color: #{{$_subtree.parent->color}};";
            {{if $_subtree.objects|@count == 0}}disabled{{/if}}
            {{if $tag_id == $_subtree.parent->tag_id}}selected{{/if}}>
      {{"&nbsp;&nbsp;&nbsp;"|str_repeat:$depth}}{{if $depth > 0}}|&ndash;{{/if}}
      {{$_subtree.parent->name}}
      ({{$_subtree.objects|@count}})
    </option>

    {{mb_include module=ccam template=inc_favoris_tag_select depth=$depth+1 tag_tree=$_subtree}}
  {{/if}}
{{/foreach}}
