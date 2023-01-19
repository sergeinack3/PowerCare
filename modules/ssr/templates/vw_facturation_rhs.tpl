{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module="ssr" script="cotation_rhs"}}
{{mb_script module="dPplanningOp" script="sejour"}}

{{if !$rhs_counts}}
  <div class="small-info">{{tr}}CRHS-none{{/tr}}</div>
{{else}}
  <table class="main">
    <tr>
      <td class="narrow">
        <script>
        Main.add(function() {
          Control.Tabs.create('tabs-rhss_no_charge', true).activeLink.up().onmousedown();
        });
        </script>
        <ul id="tabs-rhss_no_charge" class="control_tabs_vertical" style="width: 14em;">
          <li onmousedown="">
            <a href="#rhs-search" class="empty">{{tr}}Search{{/tr}} <small>(&ndash;)</small></a>
          </li>
          {{foreach from=$rhs_counts item=_rhs_count}}
            <li onmousedown="Sejour.refresh('{{$_rhs_count.mondate}}')">
              <a href="#rhs-no-charge-{{$_rhs_count.mondate}}">
                {{tr}}Week{{/tr}} {{$_rhs_count.mondate|date_format:"%V"}}
                <small class="count">({{$_rhs_count.count}})</small>
                <br />
                <small>
                  {{tr}}date.from{{/tr}} {{$_rhs_count.mondate|date_format:$conf.date}}
                  <br />
                  {{tr}}date.to{{/tr}} {{$_rhs_count.sundate|date_format:$conf.date}}
                </small>
              </a>
            </li>
          {{/foreach}}
        </ul>
      </td>
      <td>
        <div id="rhs-search">
          <form name="rhs-search" action="?" method="get" onsubmit="return Sejour.search();">
            <table class="form">
              <tr>
                <th><label for="nda">{{tr}}ssr-nda_patient{{/tr}}</label></th>
                <td><input name="nda" type="text" /></td>
                <td class="button"><button class="search" onsubmit>{{tr}}Search{{/tr}}</button></td>
              </tr>
            </table>
          </form>
          <div id="rhs-search-result"></div>
        </div>
        {{foreach from=$rhs_counts item=_rhs_count}}
          <div id="rhs-no-charge-{{$_rhs_count.mondate}}" style="display: none;"></div>
        {{/foreach}}
      </td>
    </tr>
  </table>
{{/if}}