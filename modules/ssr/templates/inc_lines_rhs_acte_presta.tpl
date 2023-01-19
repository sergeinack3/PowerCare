{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=read_only value=false}}
{{if $rhs->facture == 1}}
  {{assign var=read_only value=true}}
{{/if}}
{{assign var=days value='Ox\Mediboard\Ssr\CRHS'|static:days}}
{{assign var=rhs_id value=$rhs->_id}}
{{assign var=presta_therapeute_id value=""}}
{{assign var=lines_presta value=$rhs->_prestas_ssr}}

<table class="tbl">
  <tr>
    <th>{{mb_title class=CActePrestationSSR field=code}}</th>
    <th>{{mb_title class=CActePrestationSSR field=type}}</th>
    <th class="text">{{mb_title class=CActiviteCdARR field=libelle}}</th>
    {{foreach from=$days key=day item=litteral_day}}
      <th class="category narrow">{{mb_title class=CLigneActivitesRHS field=qty_$litteral_day}}</th>
    {{/foreach}}
  </tr>
  {{foreach from=$lines_presta item=_line}}
    {{assign var=therapeute value=$_line->_ref_executant}}

    {{if $therapeute->_id != $presta_therapeute_id}}
      <tr>
        <th class="text section" colspan="18" style="text-align: left;">
          {{mb_include module="mediusers" template="inc_vw_mediuser" mediuser=$therapeute}}
          &mdash;
          {{tr}}CEvenementSSR-back-prestas_ssr{{/tr}}
        </th>
      </tr>
    {{/if}}

    {{assign var=presta_therapeute_id value=$therapeute->_id}}
    <tr>
      <td>
        {{$_line->_ref_presta_ssr->code}}
      </td>
      <td>
        {{$_line->_ref_presta_ssr->type}}
      </td>
      <td>
        {{$_line->_ref_presta_ssr->libelle}}
      </td>

      {{foreach from=$days key=day item=litteral_day}}
        {{mb_include module=ssr template="inc_line_rhs"}}
      {{/foreach}}
    </tr>
  {{/foreach}}

  {{if !$lines_presta|@count}}
    <tr>
      <td class="empty" colspan="18">
        {{tr}}CRHS-back-lines.empty{{/tr}}
      </td>
    </tr>
  {{/if}}
</table>
