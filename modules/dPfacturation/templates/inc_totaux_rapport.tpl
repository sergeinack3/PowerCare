{{*
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=type_view value="consult"}}
{{mb_default var=nb_object value="nb_consultations"}}
{{mb_default var=type_object value="CConsultation"}}

<div id="obsolete-totals" style="background-color: #888; display: none">
  <div class="big-warning">
    <p>{{tr}}Compta-msg-total_obsolete{{/tr}}</p>
    <a class="button change" onclick="location.reload()">{{tr}}Refresh{{/tr}} {{tr}}Now{{/tr}}</a>
  </div>
</div>

<table id="totals" class="tbl" style="text-align: center;">
  {{foreach from=$reglement->_specs.emetteur->_list item=emetteur}}
    {{if $emetteur == "patient" || $type_aff}}
    <tr>
      <th class="title" colspan="9">{{tr}}CReglement|pl{{/tr}} {{tr}}CReglement.emetteur.{{$emetteur}}{{/tr}}</th>
    </tr>

    <tr>
      <th>{{mb_label object=$reglement field=mode}}</th>
      <th>{{tr}}Total{{/tr}}</th>
      {{foreach from=$reglement->_specs.mode->_list item=_mode}}
        <th>{{tr}}CReglement.mode.{{$_mode}}{{/tr}}</th>
      {{/foreach}}
      {{if isset($recapReglement.total.nb_impayes_patient|smarty:nodefaults)}}
        <th>{{tr}}CFacture-unpaid{{/tr}}</th>
      {{/if}}
    </tr>

    <tr>
      <th class="category">{{tr}}CReglement-nb{{/tr}}</th>
      {{assign var=nb_reglement_name value="nb_reglement_$emetteur"}}
      <td>{{$recapReglement.total.$nb_reglement_name}}</td>
      {{foreach from=$reglement->_specs.mode->_list item=_mode}}
      <td>{{$recapReglement.$_mode.$nb_reglement_name}}</td>
      {{/foreach}}
      {{assign var=impaye_name value="nb_impayes_$emetteur"}}
      {{if isset($recapReglement.total.$impaye_name|smarty:nodefaults)}}
        <td>{{$recapReglement.total.$impaye_name}}</td>
      {{/if}}
    </tr>

    <tr>
      <th class="category">{{tr}}CReglement-total|pl{{/tr}}</th>
      {{assign var=du_name value="du_$emetteur"}}
      <td>{{$recapReglement.total.$du_name|currency}}</td>
      {{foreach from=$reglement->_specs.mode->_list item=_mode}}
      <td>{{$recapReglement.$_mode.$du_name|currency}}</td>
      {{/foreach}}
      {{assign var=reste_name value="reste_$emetteur"}}
      {{if isset($recapReglement.total.nb_impayes_patient|smarty:nodefaults)}}
        <td>{{$recapReglement.total.$reste_name|currency}}</td>
      {{/if}}
    </tr>
    {{/if}}
  {{/foreach}}
   
  <tr>
    <th class="title" colspan="9">{{tr}}compta-recap_{{$type_view}}{{/tr}}</th>
  </tr>
  {{if $type_aff}}
    <tr>
      <th> {{tr}}Total{{/tr}} {{mb_label class=CConsultation field=secteur1}} </th>
      <td colspan="4">{{$recapReglement.total.secteur1|currency}}</td>
      <th colspan="4">
        {{if $type_view == "evt"}}{{tr}}CEvenementPatient|pl{{/tr}}{{else}}{{tr}}{{$type_object}}{{/tr}}{{/if}}
      </th>
    </tr>
    <tr>
      <th> {{tr}}Total{{/tr}} {{mb_label class=CConsultation field=secteur2}} </th>
      <td colspan="4">{{$recapReglement.total.secteur2|currency}}</td>
      <td colspan="{{if $type_aff}}4{{else}}8{{/if}}">{{$recapReglement.total.$nb_object}}</td>
    </tr>
    <tr>
      <th> {{tr}}Total{{/tr}} {{mb_label class=CConsultation field=secteur3}} </th>
      <td colspan="4">{{$recapReglement.total.secteur3|currency}}</td>
      <th colspan="4">{{mb_label class=CConsultation field=_somme}}</th>
    </tr>
    <tr>
      <th> {{tr}}Total{{/tr}} {{mb_label class=CConsultation field=du_tva}} </th>
      <td colspan="4">{{$recapReglement.total.du_tva|currency}}</td>
      <td colspan="4" class="button">
        {{$recapReglement.total.secteur1+$recapReglement.total.secteur2+$recapReglement.total.secteur3+$recapReglement.total.du_tva|currency}}
      </td>
    </tr>
    <tr>
      <th>{{tr}}CFacture-total_regle_patient{{/tr}}</th>
      <td colspan="4">{{$recapReglement.total.du_patient|currency}}</td>
      <th colspan="4">{{tr}}CFacture-total_regle{{/tr}}</th>
    </tr>
    <tr>
      <th>{{tr}}CFacture-total_regle_tiers{{/tr}}</th>
      <td colspan="4">{{$recapReglement.total.du_tiers|currency}}</td>
      <td colspan="4" class="button">
        {{$recapReglement.total.du_patient+$recapReglement.total.du_tiers|currency}}
      </td>
    </tr>
    {{if $a == "print_rapport"}}
      <tr>
        <th>{{tr}}CFacture-total_no_regle_patient{{/tr}}</th>
        <td colspan="4">{{$recapReglement.total.reste_patient|currency}}</td>
        <th colspan="4">{{tr}}CFacture-total_no_regle{{/tr}}</th>
      </tr>
      <tr>
        <th>{{tr}}CFacture-total_no_regle_tiers{{/tr}}</th>
        <td colspan="4">{{$recapReglement.total.reste_tiers|currency}}</td>
        <td colspan="4" class="button">
          {{$recapReglement.total.reste_patient+$recapReglement.total.reste_tiers|currency}}
        </td>
      </tr>
    {{/if}}
  {{else}}
    <tr>
      <th>{{mb_label class=CConsultation field=_somme}}</th>
      {{assign var=total_du value=$recapReglement.total.secteur1+$recapReglement.total.secteur2}}
      <td colspan="8">{{$total_du|currency}}</td>
    </tr>
    <tr>
      <th>{{tr}}CFacture-total_regle{{/tr}}</th>
      {{assign var=regle value=$recapReglement.total.du_patient+$recapReglement.total.du_tiers}}
      <td colspan="8">{{$regle|currency}}</td>
    </tr>
    {{if isset($recapReglement.total.nb_impayes_patient|smarty:nodefaults)}}
      <tr>
        <th>{{tr}}CFacture-total_no_regle{{/tr}}</th>
        <td colspan="8">{{$total_du-$regle|currency}}</td>
      </tr>
    {{/if}}
    {{if isset($recapReglement.total.nb_accidents|smarty:nodefaults)}}
      <tr>
        <th>{{tr}}compta-nb_accidents{{/tr}}</th>
        <td colspan="8">{{$recapReglement.total.nb_accidents}}</td>
      </tr>
    {{/if}}
  {{/if}}
</table>
