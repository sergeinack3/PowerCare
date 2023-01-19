{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  changePage = function(page) {
    oForm = getForm("filter-presta-ssr");
    $V(oForm.page_ssr, page);
    oForm.submit();
  };
</script>

<table class="main">
  <tr>
    <td>
      <form action="?" name="filter-presta-ssr" method="get">
        <input type="hidden" name="m" value="{{$m}}" />
        <input type="hidden" name="{{$actionType}}" value="{{$action}}" />
        <input type="hidden" name="page_ssr" value="{{$page}}" />
        <table class="form">
          <tr>
            <th>{{mb_title object=$presta_ssr field=code}}</th>
            <td><input name="code_ssr" type="text" value="{{$code}}" /></td>
            <th>{{mb_label object=$presta_ssr field=type}}</th>
            <td>
              <select name="type_ssr">
                <option value="all">&mdash; {{tr}}All{{/tr}}</option>
                {{foreach from=$presta_ssr->_specs.type->_list item=_type}}
                  <option value="{{$_type}}" {{if $_type == $type}}selected{{/if}}>
                    {{tr}}CPrestaSSR.type.{{$_type}}{{/tr}}
                  </option>
                {{/foreach}}
              </select>
            </td>
          </tr>
          <tr>
            <td colspan="4" class="button">
              <button class="search" type="submit" onclick="$V(this.form.page_ssr, 0);">{{tr}}Display{{/tr}}</button>
            </td>
          </tr>
        </table>
      </form>
    </td>
  </tr>
  <tr>
    <td>
      {{mb_include module=system template=inc_pagination change_page='changePage' total=$total current=$page step=$step}}
    </td>
  </tr>
  <tr>
    <td>
      <table class="main tbl">
        <tr>
          <th class="narrow">{{mb_title object=$presta_ssr field=type}}</th>
          <th class="narrow">{{mb_title object=$presta_ssr field=code}}</th>
          <th class="narrow">{{mb_title object=$presta_ssr field=libelle}}</th>
          <th>{{mb_title object=$presta_ssr field=tarif}}</th>
          <th>{{mb_title object=$presta_ssr field=type_tarif}}</th>
          <th>{{mb_title object=$presta_ssr field=description}}</th>
        </tr>
        {{foreach from=$prestas item=_presta}}
          <tr>
            <td>{{tr}}CPrestaSSR.type.{{$_presta->type}}{{/tr}}</td>
            <td>{{$_presta->code}}</td>
            <td>{{$_presta->libelle}}</td>
            <td class="narrow" style="text-align: center;">
              {{$_presta->tarif}}
            </td>
            <td class="narrow" style="text-align: center;">
              {{tr}}CPrestaSSR.type_tarif.{{$_presta->type_tarif}}{{/tr}}
            </td>
            <td class="compact text" style="width: 30%;">
              <button type="button" class="search notext" onclick="$('description-{{$_presta->prestation_id}}').toggle();" style="float: right;">{{tr}}CPrestaSSR-action-View description{{/tr}}</button>
              <div id="description-{{$_presta->prestation_id}}" style="display: none; text-align: left;">
                {{$_presta->description|smarty:nodefaults}}
              </div>
            </td>
          </tr>
          {{foreachelse}}
          <tr>
            <td colspan="6" class="empty">{{tr}}CPrestaSSR.none{{/tr}}</td>
          </tr>
        {{/foreach}}
      </table>
    </td>
  </tr>
</table>
