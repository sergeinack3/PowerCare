{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=read_only      value=false}}
{{mb_default var=mode_duplicate value=0}}
{{mb_default var=light_view     value=0}}
{{mb_default var=print          value=false}}

{{if $rhs->facture == 1}}
  {{assign var=read_only value=true}}
{{/if}}
{{assign var=days value='Ox\Mediboard\Ssr\CRHS'|static:days}}

<table class="tbl">
  <tr>
    <th class="narrow">
      {{if !$light_view && !$read_only && $rhs->_ref_lines_by_executant|@count}}
        <button type="button" class="duplicate notext compact" style="float: left;"
                onclick="CotationRHS.duplicate('{{$rhs->_id}}', '{{$rhs->sejour_id}}', 'activites');">{{tr}}Duplicate{{/tr}}</button>
      {{/if}}
    </th>
    <th colspan="2">Codes</th>
    <th>{{mb_title class=CLigneActivitesRHS field=modulateurs}}</th>
    <th>{{mb_title class=CLigneActivitesRHS field=phases}}</th>
    <th>{{mb_title class=CLigneActivitesRHS field=nb_patient_seance}}</th>
    <th>{{mb_title class=CLigneActivitesRHS field=nb_intervenant_seance}}</th>
    <th>{{mb_title class=CLigneActivitesRHS field=extension}}</th>
    <th>{{mb_title class=CLigneActivitesRHS field=commentaire}}</th>
    <th>{{mb_title class=CActiviteCdARR field=libelle}}</th>
    {{foreach from=$days key=day item=litteral_day}}
      <th class="category narrow">{{mb_title class=CLigneActivitesRHS field=qty_$litteral_day}}</th>
    {{/foreach}}
    <th class="narrow"></th>
  </tr>
  {{if !$light_view}}
    {{foreach from=$rhs->_ref_lines_by_executant key=executant_id item=_lines name=all_lines}}
      {{assign var=executant value=$rhs->_ref_executants.$executant_id}}
      <tr>
        <th class="text section" colspan="18" style="text-align: left;">

          {{if $smarty.foreach.all_lines.first}}
            <div class="me-float-right">
              {{tr}}CRHS-Light_view{{/tr}}
              <input type="checkbox" name="light_view" value="{{$light_view}}" {{if $light_view}}checked{{/if}}
                                    onchange="CotationRHS.refreshRHS('{{$rhs->_id}}', null, this.checked ? 1 : 0);" />
            </div>
          {{/if}}

          {{mb_include module="mediusers" template="inc_vw_mediuser" mediuser=$executant}}
          &mdash;
          {{$executant->_ref_intervenant_cdarr}}
        </th>
      </tr>
      {{foreach from=$_lines item=_line}}
        {{if $_line->code_activite_cdarr}} {{assign var=activite value=$_line->_ref_activite_cdarr}} {{/if}}
        {{if $_line->code_activite_csarr}} {{assign var=activite value=$_line->_ref_activite_csarr}} {{/if}}
        <tr>
          <td>
            {{if !$read_only && !$_line->auto}}
              <form name="del-line-{{$_line->_guid}}" action="?m={{$m}}" method="post" onsubmit="return CotationRHS.onSubmitLine(this);">
                {{mb_class object=$_line}}
                {{mb_key   object=$_line}}
                <input type="hidden" name="rhs_id" value="{{$rhs->_id}}" />
                <button class="notext trash" type="button" onclick="return CotationRHS.confirmDeletionLine(this.form);">
                  {{tr}}Delete{{/tr}}
                </button>
              </form>
            {{/if}}

            {{if $mode_duplicate}}
              <input type="checkbox" name="_lines_rhs[{{$_line->_id}}]" checked value="1" />
            {{/if}}
          </td>

          <td class="text">{{$activite}}</td>
          <td class="narrow">
            {{if $_line->code_activite_cdarr}} {{$activite->_ref_type_activite->code}} {{/if}}
            {{if $_line->code_activite_csarr}} {{$activite->_ref_hierarchie->code}} {{/if}}
          </td>
          <td>{{mb_value object=$_line field=modulateurs}}</td>
          <td>{{mb_value object=$_line field=phases}}</td>
          <td>{{mb_value object=$_line field=nb_patient_seance}}</td>
          <td>{{mb_value object=$_line field=nb_intervenant_seance}}</td>
          <td>{{mb_value object=$_line field=extension}}</td>
          <td>{{mb_value object=$_line field=commentaire}}</td>
          <td class="text">{{$activite->libelle}}</td>

          {{foreach from=$days key=day item=litteral_day}}
            {{mb_include module=ssr template="inc_line_rhs"}}
          {{/foreach}}

          <td>{{mb_include module=system template=inc_object_history object=$_line}}</td>
        </tr>
      {{/foreach}}
      {{foreachelse}}
      <tr>
        <td colspan="18" class="empty">{{tr}}CRHS-back-lines.empty{{/tr}}</td>
      </tr>
    {{/foreach}}
  {{else}}
    {{foreach from=$rhs->_ref_lines_by_executant_by_code key=executant_id item=_codes name=all_lines}}
      {{assign var=executant value=$rhs->_ref_executants.$executant_id}}
      <tr>
        <th class="text section" colspan="18" style="text-align: left;">

          {{if !$print && $smarty.foreach.all_lines.first}}
            <div class="me-float-right">
              {{tr}}CRHS-Light_view{{/tr}}
              <input type="checkbox" name="light_view" value="{{$light_view}}" {{if $light_view}}checked{{/if}}
                                    onchange="CotationRHS.refreshRHS('{{$rhs->_id}}', null, this.checked ? 1 : 0);" />
            </div>
          {{/if}}
          {{mb_include module="mediusers" template="inc_vw_mediuser" mediuser=$executant}}
          &mdash;
          {{$executant->_ref_intervenant_cdarr}}
        </th>
      </tr>
      {{foreach from=$_codes item=_code}}
        <tr>
          <td></td>

          <td class="text">{{$_code.activite}}</td>
          <td class="narrow">{{$_code.code}}</td>
          <td>{{$_code.modulateurs}}</td>
          <td>{{$_code.phases}}</td>
          <td>{{$_code.nb_patient_seance}}</td>
          <td>{{$_code.nb_intervenant_seance}}</td>
          <td>{{$_code.extension}}</td>
          <td>{{$_code.commentaire}}</td>
          <td class="text">{{$_code.libelle}}</td>

          {{assign var=_line value=$_code.line}}
          {{foreach from=$days key=day item=litteral_day}}
            {{mb_include module=ssr template="inc_line_rhs"}}
          {{/foreach}}

          <td></td>
        </tr>
      {{/foreach}}
      {{foreachelse}}
      <tr>
        <td colspan="18" class="empty">{{tr}}CRHS-back-lines.empty{{/tr}}</td>
      </tr>
    {{/foreach}}
  {{/if}}
</table>
