{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !@$offline}}
<h2>
  Consultation de {{$consult->_ref_patient}} par {{if $consult->_ref_praticien->isPraticien()}}le Dr{{/if}} {{$consult->_ref_praticien}} le
  {{mb_value object=$consult field=_date}}
</h2>
{{/if}}

{{mb_include module=cabinet template=inc_list_actes_ccam subject=$consult vue=complete extra=tarif}}

{{assign var=object value=$consult}}
<table class="main tbl">
  <tr>
    <th class="title" colspan="8">Codages des actes NGAP</th>
  </tr>

  <tr>
    <th class="category">{{mb_title class=CActeNGAP field=quantite}}</th>
    <th class="category">{{mb_title class=CActeNGAP field=code}}</th>
    <th class="category">{{mb_title class=CActeNGAP field=coefficient}}</th>
    <th class="category">{{mb_title class=CActeNGAP field=demi}}</th>
    <th class="category">{{mb_title class=CActeNGAP field=montant_base}}</th>
    <th class="category">{{mb_title class=CActeNGAP field=montant_depassement}}</th>
    <th class="category">{{mb_title class=CActeNGAP field=complement}}</th>
    <th class="category">{{mb_title class=CActeNGAP field=executant_id}}</th>
  </tr>
  
  {{foreach from=$object->_ref_actes_ngap item="_acte_ngap"}}
  <tr>
    <td>{{mb_value object=$_acte_ngap field="quantite"}}</td>
    <td>{{mb_value object=$_acte_ngap field="code"}}</td>
    <td>{{mb_value object=$_acte_ngap field="coefficient"}}</td>
    <td>{{mb_value object=$_acte_ngap field="demi"}}</td>
    <td style="text-align: right">{{mb_value object=$_acte_ngap field="montant_base"}}</td>
    <td style="text-align: right">{{mb_value object=$_acte_ngap field="montant_depassement"}}</td>
    <td>
      {{if $_acte_ngap->complement}}
        {{mb_value object=$_acte_ngap field="complement"}}
      {{else}}
        Aucun
      {{/if}}
    </td>

    {{assign var="executant" value=$_acte_ngap->_ref_executant}}
    <td> 
      <div class="mediuser" style="border-color: #{{$executant->_ref_function->color}};">
       {{$executant}}
      </div>
    </td>
  </tr>
  {{foreachelse}}
  <tr>
    <td colspan="8" class="empty">{{tr}}CActeNGAP.none{{/tr}}</td>
  </tr>
  {{/foreach}}
</table>