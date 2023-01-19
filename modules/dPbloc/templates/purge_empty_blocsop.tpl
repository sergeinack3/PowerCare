{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $purge}}
  <div class="small-warning">
    {{tr}}CBlocOperatoire-msg-delete{{/tr}} x {{$success_count}}
  </div>
{{/if}}

<div class="small-info">
  {{$count}} {{tr}}CBlocOperatoire-found_empty_count{{/tr}}
</div>

{{if count($failures)}}
  <div style="max-height: 500px; overflow-y: auto;">
    <table class="tbl">
      <tr>
        <th class="narrow">{{tr}}CBlocOperatoire{{/tr}}</th>
        <th>{{tr}}Message{{/tr}}</th>
      </tr>
      {{foreach from=$failures key=_bloc_id item=_msg}}
        {{assign var=bloc value=$blocs.$_bloc_id}}
        <tr>
          <td>
            <strong>{{$bloc->_view}}</strong>
            </button>
          </td>
          <td>
            <div class="text error">{{$_msg}}</div>
          </td>
        </tr>
      {{/foreach}}
    </table>
  </div>
{{/if}}

<form name="PurgeEmpty" action="?m={{$m}}" method="get" onsubmit="return CBloc.purgeEmpty(this);">
  <input name="purge" type="hidden" value="{{$purge}}" />

  <table class="form">
    <tr>
      <td>
        <label for="max">{{tr}}Max{{/tr}}</label>
        <input type="text" name="max" value="{{$max}}" />
      </td>

      <td class="button">
        <button class="search" type="button" onclick="$V(this.form.purge, '0'); this.form.onsubmit();">{{tr}}Count{{/tr}}</button>
        <button class="trash"  type="button" onclick="$V(this.form.purge, '1'); this.form.onsubmit();">{{tr}}Purge{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>