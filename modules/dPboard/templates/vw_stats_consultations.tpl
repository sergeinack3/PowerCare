{{*
 * @package Mediboard\Board
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{mb_default var=refresh value=0}}

{{if $prat->_id}}
  <script>
    let graphs = {{$graphs|@json}};
    Main.add(function () {
      graphs.each(function (g, i) {
        Flotr.draw($('graph-' + i), g.series, g.options);
      });
    });
  </script>
    {{if !$refresh}}
      <form name="filters" action="?" method="get" onsubmit="return checkForm(this)">
        <input type="hidden" name="m" value="dPboard"/>
        <input type="hidden" name="a" value="viewStatsConsultations"/>
        <input type="hidden" name="refresh" value="1"/>

        <table class="form">
          <tr>
            <th colspan="4" class="category">{{tr}}viewStats-title-consultations stats{{/tr}}</th>
          </tr>

          <tr>
            <th>
                {{mb_label object=$filterConsultation field="_date_min"}}</th>
            <td>
                {{mb_field object=$filterConsultation field="_date_min" form="filters" register=true canNull="false"}}
            </td>
            <th>
                {{mb_label object=$filterConsultation field="_date_max"}}</th>
            <td>
                {{mb_field object=$filterConsultation field="_date_max" form="filters" register=true canNull="false"}}
            </td>
          </tr>

          <tr>
            <td colspan="4" class="button">
              <button type="button" class="me-primary search"
                      onclick="onSubmitFormAjax(this.form, {}, 'graphsConsultation')">
                  {{tr}}Display{{/tr}}
              </button>
            </td>
          </tr>
        </table>
      </form>
    {{/if}}
  <div id="graphsConsultation">
      {{foreach from=$graphs item=graph key=key}}
        <div style="width: 600px; height: 350px;margin: 1em;" id="graph-{{$key}}" class="me-float-left"></div>
      {{/foreach}}
  </div>
{{/if}}
