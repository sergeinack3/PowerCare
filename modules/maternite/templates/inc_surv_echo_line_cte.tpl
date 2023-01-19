{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{mb_default var=unite       value=""}}
{{mb_default var=style_label value=""}}
{{mb_default var=class_value value=""}}

<tr>
  <td class="{{$style_label}}" style="text-align: right;">{{mb_label class=CSurvEchoGrossesse field=$cte}}</td>
  {{foreach from=$echographies item=_echographie}}
    <td class="{{$class_value}}">
      {{if $_echographie->$cte}}{{mb_value object=$_echographie field=$cte}} {{$unite}}{{elseif !in_array($cte, array("avis_dan", "opn"))}}&mdash;{{/if}}
      {{if in_array($cte, array("avis_dan", "opn")) && !$_echographie->$cte}}{{tr}}No{{/tr}}{{/if}}
    </td>
  {{/foreach}}
  <td></td>
</tr>
