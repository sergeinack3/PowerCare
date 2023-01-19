{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=tbl_class value="main tbl"}}

{{if !@$offline}}
  <script>
    Main.add(window.print);
  </script>

  </td>
  </tr>
  </table>
  {{assign var=tbl_class value="print"}}
{{/if}}

<div class="modal-content" style="text-align: left;">

<table class="{{$tbl_class}}">
  <tr>
    <th class="title" colspan="4" style="font-size: 16px; cursor: pointer;" onclick="window.print()">
      Dossier d'urgence de <span style="font-size: 20px">{{$patient->_view}}</span> {{mb_include module=patients template=inc_vw_ipp ipp=$patient->_IPP}} <br />
      né(e) le {{mb_value object=$patient field=naissance}} de sexe {{if $patient->sexe == "m"}} masculin {{else}} féminin {{/if}} <br /> <hr />
      <h4>
        <span>
          par le Dr {{$consult->_ref_praticien}}
            le {{mb_value object=$consult field=_date}}
            - Dossier {{mb_include module=planningOp template=inc_vw_numdos nda_obj=$sejour}}
        </span>
      </h4>
      <hr>
      <h4>
        <span>
          {{mb_label object=$sejour field=group_id}} : {{mb_value object=$sejour field=group_id}}
        </span>
      </h4>
    </th>
  </tr>
</table>

{{mb_include module=patients template=CPatient_complete no_header=true embed=true object=$patient}}

<br />
<table class="{{$tbl_class}}">
  <tr><th class="title">Constantes médicales</th></tr>
</table>
{{mb_include module=patients template=print_constantes}}

<br />
<table class="{{$tbl_class}}">
  <tr><th class="title" colspan="2">{{tr}}CAntecedent.more{{/tr}}</th></tr>
  <thead>
    <tr>
      <th class="title" colspan="2">
        {{$sejour->_view}}
        {{mb_include module=planningOp template=inc_vw_numdos nda_obj=$sejour}}
      </th>
    </tr>
  </thead>
  <tr>
    <th style="width: 50%;">{{mb_label object=$rpu field="motif"}}</th>
    <td>{{mb_value object=$rpu field="motif"}}</td>
  </tr>
  
  {{mb_include module=cabinet template=print_inc_antecedents_traitements}}
</table>

{{if !@$offline}}
  <br style="page-break-after: always;" />
{{else}}
  <br />
{{/if}}

<table class="{{$tbl_class}}">
  <thead>
    <tr>
      <th class="title" colspan="4">
        {{$sejour->_view}}
        {{mb_include module=planningOp template=inc_vw_numdos nda_obj=$sejour}}
      </th>
    </tr>
  </thead>
  <tr><th class="title" colspan="4">Transmissions paramédicales de passage aux urgences</th></tr>
    
  <tr>
    <th style="width: 25%;">{{mb_label object=$sejour field="entree_reelle"}} </th>
    <td style="width: 25%;">{{mb_value object=$sejour field="entree_reelle"}} </td>
    
    <th style="width: 25%;">{{mb_label object=$sejour field="sortie_reelle"}} </th>
    <td style="width: 25%;">{{mb_value object=$sejour field="sortie_reelle"}} </td>
  </tr>
  
  <tr>
    <th>{{mb_label object=$rpu field="ccmu"}} {{if $rpu->_count_rpu_reevaluations_pec > 0}}({{tr}}common-initial|f{{/tr}}){{/if}}</th>
    <td>{{mb_value object=$rpu field="ccmu"}}</td>
    
    <th>{{mb_label object=$rpu field="cimu"}} {{if $rpu->_count_rpu_reevaluations_pec > 0}}({{tr}}common-initial|f{{/tr}}){{/if}}</th>
    <td>{{mb_value object=$rpu field="cimu"}}</td>
  </tr>
  
  <tr> 
    <th>{{mb_label object=$sejour field="mode_entree"}}</th>
    <td>{{mb_value object=$sejour field="mode_entree"}}</td>

    <th>{{mb_label object=$rpu field="box_id"}}</th>
    <td>{{mb_value object=$rpu field="box_id"}}</td>
  </tr>
  
  <tr>   
    <th>{{mb_label object=$sejour field="transport"}}</th>
    <td>{{mb_value object=$sejour field="transport"}}</td>

    <th>{{mb_label object=$sejour field="provenance"}}</th>
    <td>{{mb_value object=$sejour field="provenance"}}</td>
  </tr>
  
  <tr>
    <th>{{mb_label object=$rpu field="diag_infirmier"}}</th>
    <td>{{mb_value object=$rpu field="diag_infirmier"}}</td>

    <th>{{mb_label object=$rpu field="pec_transport"}}</th>
    <td>{{mb_value object=$rpu field="pec_transport"}}</td>
  </tr>
</table>

{{if $rpu->_count_rpu_reevaluations_pec > 0}}
  {{mb_include module=urgences template=rpu/inc_table_reeval_pec print=true}}
{{/if}}

<br />
{{mb_include module=hospi template=inc_list_transmissions list_transmissions=$sejour->_ref_suivi_medical readonly=true}}

<table class="{{$tbl_class}}">
  <thead>
    <tr>
      <th class="title" colspan="2">
        {{$sejour->_view}}
        {{mb_include module=planningOp template=inc_vw_numdos nda_obj=$sejour}}
      </th>
    </tr>
  </thead>
  <tr>
    <th style="width: 50%;">Documents</th>
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
  
  <tr>
    <th>{{mb_label object=$rpu field="gemsa"}}</th>
    <td>{{mb_value object=$rpu field="gemsa"}}</td>
  </tr>
</table>

<br />
<table class="{{$tbl_class}}">
  <tr><th colspan="2" class="title">Précisions sur la sortie</th></tr>
  <tr>
    <th style="width: 50%;">{{mb_label object=$rpu field="orientation"}}</th>
    <td>{{mb_value object=$rpu field="orientation"}}</td>
  </tr>
  
  <tr>
    <th>{{mb_label object=$sejour field="destination"}}</th>
    <td>{{mb_value object=$sejour field="destination"}}</td>
  </tr>
</table>

<br />
{{mb_include module=cabinet template=print_actes readonly=true}}

{{if $dossier|@count}}
  <br />
  {{mb_include module=prescription template=inc_vw_dossier_cloture offline=1}}
{{/if}}

{{if "dPprescription"|module_active}}
  <br />
  <table class="tbl print_prescription" style="page-break-after: always;">
    <thead>
      <tr>
        <th class="title">
          {{$sejour->_view}}
          {{mb_include module=planningOp template=inc_vw_numdos nda_obj=$sejour}}
        </th>
      </tr>
    </thead>
    <tr>
      <th class="title">
        Prescription
      </th>
    </tr>
    {{if $prescription->_ref_lines_med_comments.med|@count || $prescription->_ref_lines_med_comments.comment|@count}}
    <tr>
      <th>
        Médicaments
      </th>
    </tr>
    {{/if}}
    {{foreach from=$prescription->_ref_lines_med_comments.med key=atc_code item=lines_med_by_atc}}
      <tr>
        <th class="section">
          {{assign var=_libelle_ATC value=$atc_classes.$atc_code}}
          {{$_libelle_ATC}}
        </th>
      </tr>  
      {{foreach from=$lines_med_by_atc item=line_med}}
        <tr>
          <td class="text">
            {{mb_include module="dPprescription" template="inc_print_medicament" med=$line_med nodebug=true print=false dci=0}}
          </td>
        </tr>
      {{/foreach}}
    {{/foreach}}
  
    {{foreach from=$prescription->_ref_lines_med_comments.comment item=line_med_comment}}
      <tr>
        <td class="text">
          {{mb_include module="dPprescription"  template="inc_print_commentaire" comment=$line_med_comment nodebug=true}}
        </td>
      </tr>
    {{/foreach}}


    {{if $prescription->_ref_prescription_line_mixes|@count}}
      {{foreach from=$prescription->_ref_prescription_line_mixes_by_type key=type item=_prescription_line_mixes}}
        <tr>
          <th>{{tr}}CPrescriptionLineMix.type_line.{{$type}}{{/tr}}</th>
        </tr>
        {{foreach from=$_prescription_line_mixes item=_prescription_line_mix}}
          <tr>
            <td class="text">
              {{mb_include module="dPprescription" template="inc_print_prescription_line_mix" perf=$_prescription_line_mix nodebug=true}}
            </td>
          </tr>
        {{/foreach}}
      {{/foreach}}
    {{/if}}
    
    {{foreach from=$prescription->_ref_lines_elements_comments key=_chap item=_lines_by_chap}}
      {{if $_lines_by_chap|@count}}
        <tr>
          <th>
            {{tr}}CCategoryPrescription.chapitre.{{$_chap}}{{/tr}}
          </th>
        </tr>
      {{/if}}
      {{if "dPprescription general display_cat_for_elt"|gconf}}
        {{foreach from=$_lines_by_chap item=_lines_by_cat}}
          {{assign var=cat_displayed value="0"}}
          {{if array_key_exists('element', $_lines_by_cat) || array_key_exists('comment', $_lines_by_cat)}}
            <tr>
              <td class="text">
              {{if array_key_exists('comment', $_lines_by_cat)}}
                {{foreach from=$_lines_by_cat.element item=line_elt name=foreach_lines_a}}
                  {{if $smarty.foreach.foreach_lines_a.first}}
                    {{assign var=cat_displayed value="1"}}
                    <strong>{{$line_elt->_ref_element_prescription->_ref_category_prescription->nom}} :</strong>
                  {{/if}}
                  {{mb_include module="dPprescription" template="inc_print_element" elt=$line_elt nodebug=true}}
                {{/foreach}}
              {{/if}}
              {{if array_key_exists('comment', $_lines_by_cat)}}
                {{foreach from=$_lines_by_cat.comment item=line_elt_comment name=foreach_lines_b}}
                  {{if $smarty.foreach.foreach_lines_b.first && !$cat_displayed}}
                    <strong>{{$line_elt_comment->_ref_category_prescription->nom}} :</strong>
                  {{/if}}
                  <li>
                     ({{$line_elt_comment->_ref_praticien->_view}})
                     {{$line_elt_comment->commentaire|nl2br}}
                  </li>
                {{/foreach}}
              {{/if}}
              </td>
            </tr>
          {{/if}}
        {{/foreach}}
      {{else}}
        {{foreach from=$_lines_by_chap item=_lines_by_cat}}
          {{if array_key_exists('element', $_lines_by_cat)}}
            {{foreach from=$_lines_by_cat.element item=line_elt}}
              <tr>
                <td class="text">
                   {{mb_include module="dPprescription" template="inc_print_element" elt=$line_elt nodebug=true}}
                </td>
              </tr>
            {{/foreach}}
          {{/if}}
          {{if array_key_exists('comment', $_lines_by_cat)}}
            {{foreach from=$_lines_by_cat.comment item=line_elt_comment}}
              <tr>
                <td class="text">
                   <li>
                     ({{$line_elt_comment->_ref_praticien->_view}})
                     {{$line_elt_comment->commentaire|nl2br}}
                  </li>
                </td>
              </tr>
            {{/foreach}}
          {{/if}}
        {{/foreach}}
      {{/if}}
    {{/foreach}}
  </table>
{{/if}}
{{if "forms"|module_active}}
  <table class="main tbl">
    <tr>
      <th class="title">{{tr}}CExClass|pl{{/tr}}</th>
    </tr>
    <thead>
      <tr>
        <th class="title">
          {{$sejour->_view}}
            {{mb_include module=planningOp template=inc_vw_numdos nda_obj=$sejour}}
        </th>
      </tr>
    </thead>
    <tr>
      <td>
        <div id="ex-objects-{{$sejour->_id}}">{{$formulaires|smarty:nodefaults}}</div>
      </td>
    </tr>
  </table>
{{/if}}
</div>

{{if !@$offline}}
<table>
<tr>
<td>
{{/if}}