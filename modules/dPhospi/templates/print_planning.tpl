{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var="col1" value="dPhospi print_planning col1"|gconf}}
{{assign var="col2" value="dPhospi print_planning col2"|gconf}}
{{assign var="col3" value="dPhospi print_planning col3"|gconf}}
{{assign var="modele_used" value="dPhospi print_planning modele_used"|gconf}}

<table class="tbl">
  <tr class="clear">
    <th colspan="16">
      <h1>
        <a href="#" onclick="window.print()">
          Planning du {{$filter->_date_min|date_format:$conf.datetime}}
          au {{$filter->_date_max|date_format:$conf.datetime}}
          : {{$total}} séjour(s)
          <br />
          Filtrés sur : {{mb_label class=CSejour field=$filter->_horodatage}}
        </a>
      </h1>
    </th>
  </tr>
    {{foreach from=$listDays key=key_day item=curr_day}}
        {{if $filter->_by_date}}
            {{assign var=nb_sejour value=0}}
            {{foreach from=$curr_day key=key_prat item=curr_prat}}
                {{math equation='x+y' assign=nb_sejour x=$nb_sejour y=$curr_prat.sejours|@count}}
            {{/foreach}}
          <tr class="clear">
            <td colspan="{{if $prestation->_id}}17{{else}}16{{/if}}">
              <h2>
                <strong>
                    {{$key_day|date_format:$conf.longdate}}
                </strong>- {{mb_label class=CSejour field=$filter->_horodatage}} x {{$nb_sejour}}
              </h2>
            </td>
          </tr>
        {{/if}}
        {{foreach from=$curr_day key=key_prat item=curr_prat name=_plages}}
            {{assign var="praticien" value=$curr_prat.praticien}}
            {{if !$filter->_by_date}}
              <tr class="clear">
                <td colspan="{{if $prestation->_id}}17{{else}}16{{/if}}">
                  <h2>
                    <strong>
                        {{$key_day|date_format:$conf.longdate}}
                      - Dr {{$praticien->_view}}
                    </strong>
                    - {{mb_label class=CSejour field=$filter->_horodatage}}
                    x {{$curr_prat.sejours|@count}}
                  </h2>
                </td>
              </tr>
            {{/if}}

            {{if !$filter->_by_date || $smarty.foreach._plages.first}}
                {{mb_include module=hospi template=inc_view_planning_title}}
            {{/if}}

            {{assign var=horodatage value=$filter->_horodatage}}
            {{if $modele_used == "modele1"}}
                {{mb_include module=hospi template=inc_view_planning_content1}}
            {{else}}
                {{mb_include module=hospi template=inc_view_planning_content}}
            {{/if}}
        {{/foreach}}
    {{/foreach}}
</table>
