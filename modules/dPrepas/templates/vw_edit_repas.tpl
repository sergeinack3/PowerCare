{{*
 * @package Mediboard\Repas
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  function viewplat(menu_id) {
    var url = new Url("repas", "httpreq_vw_menu");
    {{if $repas->repas_id}}
    url.addParam("repas_id", {{$repas->repas_id}});
    {{/if}}
    url.addParam("menu_id", menu_id);
    url.requestUpdate("listPlat");
  }

  function norepas() {
    var url = new Url("repas", "httpreq_vw_menu");
    {{if $repas->repas_id}}
    url.addParam("repas_id", {{$repas->repas_id}});
    {{/if}}
    url.addParam("menu_id", "");
    url.requestUpdate("listPlat", submitFormRepas);
  }

  function submitFormRepas() {
    oForm = document.editMenu;
    //oForm.submit();
  }

  {{if $repas->repas_id}}
  Main.add(function () {
    {{if $repas->menu_id}}
    viewplat({{$repas->menu_id}});
    {{else}}
    norepas();
    {{/if}}
  });
  {{/if}}
</script>

<form name="editMenu" action="?m={{$m}}&tab=vw_edit_repas" method="post" onsubmit="return checkForm(this)">
  <input type="hidden" name="m" value="repas" />
  <input type="hidden" name="dosql" value="do_repas_aed" />
  {{mb_key object=$repas}}
  <input type="hidden" name="affectation_id" value="{{$affectation->affectation_id}}" />
  <input type="hidden" name="typerepas_id" value="{{$typeRepas->typerepas_id}}" />
  <input type="hidden" name="date" value="{{$date}}" />
  <input type="hidden" name="del" value="0" />

  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$repas}}

    <tr>
      <th>
        <strong>Chambre</strong>
      </th>
      <td>
        {{$affectation->_ref_lit}}
      </td>
      <td rowspan="5" class="halfPane" id="listPlat"></td>
    </tr>

    <tr>
      <th>
        <strong>{{tr}}Date{{/tr}}</strong>
      </th>
      <td>
        {{$date|date_format:$conf.longdate}}
      </td>
    </tr>

    <tr>
      <th>
        <strong>Type de Repas</strong>
      </th>
      <td>
        {{$typeRepas->nom}}
      </td>
    </tr>

    <tr>
      <td colspan="2" class="button">
        <button type="button" class="submit" onclick="norepas();">Ne pas prévoir de repas</button>
      </td>
    </tr>

    <tr>
      <td colspan="2" class="button">
        {{if $listRepas|@count}}
          <table class="tbl">
            <tr>
              <th class="category">Menu</th>
              <th class="category">Diabétique</th>
              <th class="category">Sans sel</th>
              <th class="category">Sans résidu</th>
            </tr>
            {{foreach from=$listRepas item=curr_repas}}
              <tr>
                <td class="text">
                  <a href="#" onclick="viewplat({{$curr_repas->menu_id}})">
                    {{$curr_repas->nom}}
                  </a>
                </td>
                <td class="button">{{if $curr_repas->diabete}}<strong>{{tr}}Yes{{/tr}}</strong>{{/if}}</td>
                <td class="button">{{if $curr_repas->sans_sel}}<strong>{{tr}}Yes{{/tr}}</strong>{{/if}}</td>
                <td class="button">{{if $curr_repas->sans_residu}}<strong>{{tr}}Yes{{/tr}}</strong>{{/if}}</td>
              </tr>
            {{/foreach}}
          </table>
        {{else}}
          Pas de Repas disponible
        {{/if}}
      </td>
    </tr>
  </table>
</form>