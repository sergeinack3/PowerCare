{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main tabview me-margin-top-0">
  <tr>
    <th class="title" colspan="2">
      {{tr}}CAntecedent.more{{/tr}}
    </th>
  </tr>
  <tr>
    {{foreach from=$tab_atc key=nom item=antecedents}}
      <td style="width: 50%;" class="cell-layout">
        <table class="tbl main tabview me-no-box-shadow me-margin-top-0">
          <tr>
            <th>{{tr}}{{$nom}}{{/tr}}</th>
          </tr>
          {{foreach from=$antecedents key=name item=cat}}
            {{if $name != "alle" && $cat|@count}}
              <tr>
                <th class="section">
                  {{tr}}CAntecedent.type.{{$name}}{{/tr}}
                </th>
              </tr>
              {{foreach from=$cat item=ant}}
                <tr>
                  <td class="text">
                    {{if $ant->date}}
                      {{mb_value object=$ant field=date}}:
                    {{/if}}
                    {{$ant->rques}}
                  </td>
                </tr>
                {{if $ant->_ref_hypertext_links|@count}}
                  <tr>
                    <td>
                      <ul>
                        {{foreach from=$ant->_ref_hypertext_links item=_link}}
                          <li>
                            <a href="{{$_link->link}}" target="_blank">{{$_link->name}}</a>
                          </li>
                        {{/foreach}}
                      </ul>
                    </td>
                  </tr>
                {{/if}}
              {{/foreach}}
            {{/if}}
            {{foreachelse}}
            {{if !$ant_communs|@count}}
              <tr>
                <td class="empty">{{tr}}CAntecedent.none{{/tr}}</td>
              </tr>
            {{/if}}
          {{/foreach}}
        </table>
      </td>
    {{/foreach}}
  </tr>
</table>
<table class="tbl main tabview me-no-box-shadow me-margin-top-0">
  {{foreach from=$ant_communs key=name item=cat}}
    <tr>
      <th class="section" colspan="2">
        {{tr}}CAntecedent.type.{{$name}}{{/tr}}
      </th>
    </tr>
    {{foreach from=$cat item=ant}}
      <tr>
        <td colspan="2" class="text">
          {{if $ant->date}}
            {{mb_value object=$ant field=date}}:
          {{/if}}
          {{$ant->rques}}
        </td>
      </tr>
      {{if $ant->_ref_hypertext_links|@count}}
        <tr>
          <td>
            <ul>
              {{foreach from=$ant->_ref_hypertext_links item=_link}}
                <li>
                  <a href="{{$_link->link}}" target="_blank">{{$_link->name}}</a>
                </li>
              {{/foreach}}
            </ul>
          </td>
        </tr>
      {{/if}}
    {{/foreach}}
  {{/foreach}}
</table>