{{*
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module="system" script="object_selector"}}
{{mb_script module="patients" script="autocomplete"}}
{{mb_script module="mediusers" script="CFunctions"}}

<script>
  Main.add(function() {
    getForm('listFilter').onsubmit();
  });
</script>

{{if $can->edit}}
  <div class="me-margin-top-4 me-padding-left-8">
    <a href="#" class="button new" onclick="CFunctions.editFunction('0');">
      {{tr}}CFunctions-title-create{{/tr}}
    </a>
    <button class="fas fa-external-link-alt" onclick="CFunctions.vwExport();" type="button">
      {{tr}}CFunctions-export{{/tr}}
    </button>
    <button class="import" onclick="CFunctions.vwImport();" type="button">
      {{tr}}CFunctions-import{{/tr}}
    </button>
  </div>
{{/if}}

<div id="functions" class="me-padding-0">
  <table class="main me-w100 me-margin-top-4">
    <tr>
      <td>
        <form name="listFilter" action="?m={{$m}}" method="get" onsubmit="return onSubmitFormAjax(this, null, 'list_functions')">
          <input type="hidden" name="m" value="{{$m}}" />
          <input type="hidden" name="a" value="ajax_search_function" />
          <input type="hidden" name="page" value="{{$page}}" onchange="this.form.onsubmit()"/>
          <input type="hidden" name="order_col" value="text"/>
          <input type="hidden" name="order_way" value="ASC""/>

          <table class="main layout">
            <tr>
              <td class="separator expand" onclick="MbObject.toggleColumn(this, $(this).next())"></td>

              <td>
                <table class="form">
                  <tr>
                    <th> Mots clés : </th>
                    <td> <input type="text" name="filter" value="" style="width: 20em;" onchange="$V(this.form.page, 0)" /> </td>

                    <th>Type</th>
                    <td>
                      <label>{{tr}}All{{/tr}} <input name="type" value="" type="radio"
                                                     {{if $type != "administratif" && $type != "cabinet"}}checked{{/if}}
                                                     onchange="$V(this.form.page, 0, false)"/></label>
                      <label>Administratif <input name="type" value="administratif" type="radio"
                                                  {{if $type == "administratif"}}checked{{/if}}
                                                  onchange="$V(this.form.page, 0, false)"/></label>
                      <label>Cabinet <input name="type" value="cabinet" type="radio"
                                            {{if $type == "cabinet"}}checked{{/if}}
                                            onchange="$V(this.form.page, 0, false)"/></label>
                    </td>

                    <th> Inactif </th>
                    <td>
                      <label>{{tr}}All{{/tr}} <input name="inactif" value="" type="radio" {{if !$inactif}}checked{{/if}} onchange="$V(this.form.page, 0, false)"/></label>
                      <label>Inactifs <input name="inactif" value="1" type="radio" {{if $inactif}}checked{{/if}} onchange="$V(this.form.page, 0, false)"/></label>
                    </td>
                  </tr>
                  <tr>
                    <td colspan="6">
                      <button type="submit" class="search">{{tr}}Filter{{/tr}}</button>
                    </td>
                  </tr>
                </table>
              </td>
            </tr>
          </table>
        </form>
      </td>
    </tr>
  </table>

  <div id="list_functions" class="me-align-auto"></div>
</div>