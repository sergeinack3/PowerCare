{{*
 * @package Mediboard\Ccam
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=no_filter value=""}}
{{if $no_filter}}
  <div class="small-info">{{tr}}{{$no_filter}}{{/tr}}</div>
  {{mb_return}}
{{/if}}

{{mb_default var=no_code_for_date value=""}}
{{if $no_code_for_date}}
  <div class="small-info">{{tr}}{{$no_code_for_date}}{{/tr}}</div>
  {{mb_return}}
{{/if}}

<script>
  changePage = function (page) {
    $V(getForm('multiple_form').page, page);
  }
</script>

{{mb_include module=system template=inc_pagination total=$nbResultat current=$page change_page='changePage'}}

<table class="tbl">
  {{foreach from=$codes item=_code key=_key}}
    {{assign var=code_ccam value=$_code->_ref_code_ccam}}
    {{if $_key is div by 2}}
      <tr>
    {{/if}}
    <td class="text" style="width:50%;">
      <span class="compact" style="float: right;">
        {{tr}}CDatedCodeCCAM.type.{{$_code->type}}{{/tr}}
      </span>
      <strong>
        <a onclick="CCodageCCAM.show_code('{{$_code->code}}','{{$date_demandee}}');" href="#">
          {{$_code->code}}
        </a>
      </strong>
      <br />
      {{$_code->libelleLong}}
      <br />
      Date de création : {{$code_ccam->date_creation}}
      <br />
      {{foreach name=first from=$code_ccam->_ref_infotarif item=_infotarif}}
        {{if $smarty.foreach.first.first}}
          Dernière date d'effet : {{$_infotarif->date_effet}}
        {{/if}}
      {{/foreach}}
    </td>
    {{if ($_key+1) is div by 2 or ($_key+1) == $codes|@count}}
      </tr>
    {{/if}}
  {{/foreach}}
</table>