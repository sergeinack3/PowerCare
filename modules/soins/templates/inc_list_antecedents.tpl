{{*
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl me-no-box-shadow me-margin-top-0">
  {{foreach from=$antecedents key=name item=cat}}
    {{if $name != "alle" && is_array($cat) && $cat|@count}}
      <tr>
        <th>
          {{tr}}CAntecedent.type.{{$name}}{{/tr}}
        </th>
      </tr>
      {{foreach from=$cat item=ant}}
        <tr>
          <td class="text" {{if $ant->majeur}}style="color: #f00;" {{elseif $ant->important}}style="color: #fd7d26;"{{/if}}>
            {{if $dossier_medical->object_class == 'CSejour'}}
            <strong>
              {{/if}}
              {{if $ant->date}}
                {{mb_value object=$ant field=date}}:
              {{/if}}
              {{$ant->rques}}
              {{if $dossier_medical->object_class == 'CSejour'}}
            </strong>
            {{/if}}
          </td>
        </tr>
        {{if is_array($ant->_ref_hypertext_links) && $ant->_ref_hypertext_links|@count}}
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
  {{/foreach}}
</table>

{{if is_array($atcd_absence) && $atcd_absence|@count}}
  <table class="tbl me-no-box-shadow">
  <tr>
    <th class="title">{{tr}}CAntecedent-No antecedent|pl{{/tr}}</th>
  </tr>
  {{foreach from=$atcd_absence item=ant}}
    {{if $ant->type != "alle" && $ant}}
      <tr>
        <th>
          {{tr}}CAntecedent.type.{{$ant->type}}{{/tr}}
        </th>
      </tr>
      <tr>
        <td class="text" {{if $ant->majeur}}style="color: #f00;" {{elseif $ant->important}}style="color: #fd7d26;"{{/if}}>
          {{if $dossier_medical->object_class == 'CSejour'}}
          <strong>
            {{/if}}
            {{if $ant->date}}
              {{mb_value object=$ant field=date}}:
            {{/if}}
            {{$ant->rques}}
            {{if $dossier_medical->object_class == 'CSejour'}}
          </strong>
          {{/if}}
        </td>
      </tr>
    {{/if}}
  {{/foreach}}
</table>
{{/if}}
