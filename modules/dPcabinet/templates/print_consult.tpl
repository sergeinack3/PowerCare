{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !@$offline}}
  <script type="text/javascript">
    Main.add(window.print);
  </script>
  <button class="print not-printable" onclick="window.print()">{{tr}}Print{{/tr}}</button>
  </td>
  </tr>
  </table>
  
  {{assign var=tbl_class value="print"}}
{{/if}}

<table class="{{$tbl_class}}">
  <tr>
    <th class="title" colspan="10" style="font-size: 16px;">
      Dossier de consultation de 
      <span style="font-size: 20px;">{{$patient->_view}}</span> 
      {{mb_include module=patients template=inc_vw_ipp ipp=$patient->_IPP}} 
      <br />
      né(e) le {{mb_value object=$patient field=naissance}} ({{mb_value object=$patient field="_age"}})
      de sexe {{tr}}CPatient.sexe.{{$patient->sexe}}{{/tr}} <br />
      {{if $consult->_ref_grossesse}}
          {{$consult->_sa}} {{tr}}CDepistageGrossesse-_sa{{/tr}} + {{$consult->_ja}} J
          <br />
          {{tr var1=$consult->_ref_grossesse->terme_prevu|date_format:$conf.date}}CGrossesse-Expected term the %s{{/tr}}
      {{/if}}
      <br />
      <hr />
      <span style="font-size: 14px">
        par {{if $consult->_ref_praticien->isPraticien()}}le Dr{{/if}} {{$consult->_ref_praticien}}
        le {{mb_value object=$consult field=_date}}
        - Dossier {{mb_include module=planningOp template=inc_vw_numdos nda_obj=$sejour}}
      </span>
    </th>
  </tr>

  {{mb_include module=cabinet template=print_inc_dossier_medical}}
</table>
{{mb_include module=patients template=print_constantes}}

<table class="{{$tbl_class}}">
  {{mb_include module=cabinet template=print_inc_antecedents_traitements}}
</table>

{{if !@$offline}}
  <br style="page-break-after: always;" />
{{/if}}

{{if $consult->_ref_suivi_grossesse}}
  <table class="{{$tbl_class}}">
  {{mb_include module=maternite template=print_suivi_grossesse}}
  </table>
{{/if}}

{{mb_include module=hospi template=inc_list_transmissions readonly=true list_transmissions=$sejour->_ref_suivi_medical}}

<table class="{{$tbl_class}}">
  <tr>
    <th>Documents</th>
    <td>
      {{foreach from=$consult->_ref_documents item=_document}}
        {{$_document->_view}} <br />
      {{/foreach}}
    </td>
  </tr>
  
  <tr>
    <th>{{mb_label object=$sejour field="mode_sortie"}}</th>
    <td>{{mb_value object=$sejour field="mode_sortie"}}</td>
  </tr>
  
</table>

<table class="{{$tbl_class}}">
  <tr><th class="category" colspan="10">{{tr}}CCodable-actes{{/tr}}</th></tr>
</table>

{{mb_include module=cabinet template=print_actes readonly=true}}

{{if !@$offline}}
<table>
<tr>
<td>
{{/if}}
