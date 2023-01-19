{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="filter-users" method="get">
  <input type="hidden" name="current" value="{{$current}}" />
  <input type="hidden" name="interv" value="{{$interv}}"/>
</form>

{{mb_include module=system template=inc_pagination change_page="CodeIntervenant.changePage"}}
<table class="tbl">
  <tr>
    <th>{{mb_title object=$mediuser field=_user_last_name}}</th>
    <th>{{mb_title object=$mediuser field=code_intervenant_cdarr}}</th>
  </tr>
  {{foreach from=$mediusers item=_mediuser}}
    <tr>
      <td>{{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_mediuser}}</td>
      <td>
        <form name="mediuser-{{$_mediuser->_id}}" action="?m={{$m}}" method="post" onsubmit="return onSubmitFormAjax(this);">
          <input type="hidden" name="m" value="mediusers" />
          <input type="hidden" name="dosql" value="do_mediusers_aed" />
          <input type="hidden" name="user_id" value="{{$_mediuser->_id}}" />
          <input type="hidden" name="del" value="0" />

          <select name="code_intervenant_cdarr" onchange="this.form.onsubmit()">
            <option value="">&mdash; aucun code</option>
            {{foreach from=$intervenants item=_interv}}
              <option value="{{$_interv->code}}" {{if $_interv->code == $_mediuser->code_intervenant_cdarr}}selected{{/if}}>
                {{$_interv->_view}}
              </option>
            {{/foreach}}
          </select>
        </form>
      </td>
    </tr>
  {{/foreach}}
</table>
