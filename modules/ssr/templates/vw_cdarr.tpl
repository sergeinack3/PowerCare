{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=ssr script=cdarr}}
<script>
  function changePage(page){
    oForm = getForm("filter-activite");
    $V(oForm.current, page);
    oForm.submit();
  }
</script>

<table class="main">
  <tr>
    <td>
      <form action="?" name="filter-activite" method="get" >
        <input type="hidden" name="m" value="{{$m}}" />
        <input type="hidden" name="{{$actionType}}" value="{{$action}}" />
        <input type="hidden" name="dialog" value="{{$dialog}}" />
        <input type="hidden" name="current" value="{{$current}}" />
        <table class="form">
          <tr>
            <th>{{tr}}Keywords{{/tr}}</th>
            <td><input name="code" type="text" value="{{$activite->code}}" /></td>
            <th>{{mb_label object=$activite field=type}}</th>
            <td>
              <select name="type">
                <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
                {{foreach from=$listTypes item=_type}}
                <option value="{{$_type->code}}" {{if $_type->code == $activite->type}}selected="selected"{{/if}}>
                  {{$_type->_view}}
                </option>
                {{/foreach}}
              </select>
            </td>
          </tr>
          <tr>
            <td colspan="4" class="button">
              <button class="search" type="submit">{{tr}}Display{{/tr}}</button>
            </td>
          </tr>
        </table>
      </form>
    </td>
  </tr>
  <tr>
    <td>
      {{mb_include module=system template=inc_pagination change_page=changePage}}
    </td>
  </tr>
  <tr>
    <td>
      <table class="tbl">
        <tr>
          <th>{{mb_title object=$activite field=type}}</th>
          <th>{{mb_title object=$activite field=code}}</th>
          <th>{{mb_title object=$activite field=libelle}}</th>
          <th colspan="3" class="narrow">
            <label title="{{tr}}ssr-usage_prescription{{/tr}}">{{tr}}ssr-usage_prescription-court{{/tr}}</label>
          </th>
        </tr>
        {{foreach from=$listActivites item=_activite}}
        <tr>
          <td>{{$_activite->type|emphasize:$activite->code:"u"}}</td>
          <td>{{$_activite->code|emphasize:$activite->code:"u"}}</td>
          <td>{{$_activite->libelle|emphasize:$activite->code:"u"}}</td>
          <td class="narrow" style="text-align: center;">
            {{if $_activite->_count_elements}}
              {{$_activite->_count_elements}}
            {{/if}}
          </td>
          <td class="narrow" style="text-align: center;">
            {{if $_activite->_count_actes}}
              {{$_activite->_count_actes}}
            {{/if}}
          </td>
          <td class="narrow">
            <button class="compact search notext" onclick="CdARR.viewActiviteStats('{{$_activite->code}}')">
              {{tr}}Stats{{/tr}}
            </button>
          </td>
        </tr>
        {{foreachelse}}
        <tr>
          <td colspan="6" class="empty">{{tr}}CActiviteCdARR.none{{/tr}}</td>
        </tr>
        {{/foreach}}
      </table>
    </td>
  </tr>
</table>