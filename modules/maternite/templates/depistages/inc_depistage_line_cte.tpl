{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{mb_default var=unite       value=""}}
{{mb_default var=style_label value=""}}
{{mb_default var=class_value value=""}}
{{mb_default var=no_value    value=false}}

<tr>
  <td class="{{$style_label}}" style="text-align: right;">{{mb_label class=CDepistageGrossesse field=$cte}}</td>
  {{foreach from=$grossesse->_back.depistages item=depistage}}
    {{if $cte == "brb"}}
      {{assign var=unite value='Ox\Core\CAppUI::tr'|static_call:"CDepistageGrossesse.unite_brb.`$depistage->unite_brb`"}}
    {{/if}}
    <td class="{{$class_value}}">
      {{if $depistage->$cte}}
        {{mb_value object=$depistage field=$cte}} {{$unite}}
      {{elseif $no_value}}
        &mdash;
      {{/if}}
    </td>
  {{/foreach}}
  <td></td>
</tr>