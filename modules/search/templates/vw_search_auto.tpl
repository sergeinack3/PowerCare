{{*
 * @package Mediboard\Search
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<!--Vue de recherche automatique utilisée dans le dossier de soins (prescription, pharmacie) et le pmsi-->

{{mb_script module=search script=search}}
<script>
  Main.add(function () {
    var tab = Control.Tabs.create('tabs-favoris', true, {
      afterChange: function (container) {
        switch (container.id) {
          case "tab-General"    :
            var form = getForm("esSearch");
            form.words.focus();
            break;
          default :
            break;
        }
      }
    });
  });
</script>
<table class="main layout" id="table_main">
  <tr>
    <td class="narrow" style="vertical-align: top">
      <ul id="tabs-favoris" class="control_tabs_vertical" style="width: 15em">
        <!--Vue de recherche classique-->
        <li>
          <a href="#tab-General" style="line-height: 1em">{{tr}}CSearch classic search{{/tr}}</a>
        </li>
        {{foreach from=$results key=_search item=_result}}
            {{assign var=bookmark value=$_result->getBookmark()}}
            <li title="{{tr}}mod-search-auto-keyword{{/tr}} : {{$bookmark->entry}}">
            <a href="#tab-{{$_search}}" style="line-height: 1em" class="{{if $_result->getTotal() == 0}}empty{{/if}}">
              {{if $bookmark->titre}}
                <span class="text">{{$bookmark->titre}} ({{$_result->getTotal()}})</span>
              {{else}}
                <span>{{tr}}mod-search-auto-no-title{{/tr}} ({{$_result->getTotal()}})</span>
              {{/if}}
            </a>
          </li>
        {{/foreach}}
      </ul>
    </td>
    <td style="vertical-align: top">
      <div id="tab-General" style="display: none;">
        <!--Vue de recherche classique-->
        {{mb_include module=search template=vw_search history=null  thesaurus=null contexte=$contexte sejour_id=$sejour_id}}
      </div>
      {{foreach from=$results key=_search item=_result}}
        <div id="tab-{{$_search}}" style="display: none;">
          {{mb_include module=search template=inc_results_search results=$_result pagination=false aggregate=false words=null filters_state=false}}
        </div>
      {{/foreach}}
    </td>
  </tr>
</table>
