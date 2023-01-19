{{*
 * @package Mediboard\OpenData
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function() {
    Control.Tabs.create('tabs-hd-za', true);

    {{foreach from=$fields key=_annee item=_fields}}
      $("zone-attract-{{$_annee}}").fixedTableHeaders();
    {{/foreach}}

  });
</script>

<table class="main layout">
  <tr>
    <td width="10%">
      <ul class="control_tabs_vertical" id="tabs-hd-za">
        {{foreach from=$fields key=_annee item=_fields}}
          <li><a href="#zone-attract-{{$_annee}}">{{$_annee}}</a></li>
        {{/foreach}}
      </ul>
    </td>
    <td>
      {{foreach from=$fields key=_annee item=_fields_annee}}
        <div id="zone-attract-{{$_annee}}" style="display: none">
          <table class="main tbl">
            <tbody>
            {{foreach from=$_fields_annee item=_fields}}
              <tr>
                {{foreach from=$_fields item=_field}}
                  <td {{if $_field|is_numeric}}align="right"{{/if}} {{if $_fields.zone == $etab->raison_sociale}}class="warning"{{/if}}>
                    {{if $_field == 'N. Calc.'}}
                      {{tr}}mod-openData-hd-data-no-calc{{/tr}}
                    {{elseif $_field == 'N. Conc.'}}
                      {{tr}}mod-openData-hd-data-no-conc{{/tr}}
                    {{else}}
                      {{if $_field|is_numeric}}
                        {{$_field|number_format:0:',':' '}}
                      {{else}}
                        {{$_field}}
                      {{/if}}
                    {{/if}}
                  </td>
                {{/foreach}}
              </tr>
            {{/foreach}}
            </tbody>

            <thead>
            <tr>
              {{foreach from=$labels item=_label}}
                <th class="text">
                  {{mb_title class='CHDActiviteZone' field=$_label}}
                  {{if array_key_exists($_label, $pages)}}
                    <br/>
                    <button class="help notext" type="button" onclick="openFieldDetails({{$pages.$_label}});">
                      {{tr}}mod-openData-hospiDiag-display-infos{{/tr}}
                    </button>
                  {{/if}}
                </th>
              {{/foreach}}
            </tr>
            </thead>
          </table>
        </div>
      {{/foreach}}
    </td>
  </tr>
</table>