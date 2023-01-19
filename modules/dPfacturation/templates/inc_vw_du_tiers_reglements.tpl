{{*
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main tbl">
  <tr>
    <th class="category" style="width: 50%;">
      {{if isset($facture|smarty:nodefaults)}}
        {{mb_include module=system template=inc_object_notes object=$facture}}
      {{/if}}
      {{mb_label class=CReglement field=mode}}
      ({{mb_label class=CReglement field=banque_id}})
    </th>
    <th class="category">{{mb_label class=CReglement field=reference}}</th>
    <th class="category">{{mb_label class=CReglement field=tireur}}</th>
    <th class="category narrow">{{mb_label class=CReglement field=montant}}</th>
    <th class="category narrow">{{mb_label class=CReglement field=date}}</th>
  </tr>
  
  <!--  Liste des reglements deja effectués -->
  {{foreach from=$object->_ref_reglements item=_reglement}}
  <tr>
    <td>
      {{mb_value object=$_reglement field=mode}}
      {{if $_reglement->_ref_banque->_id}}
        ({{$_reglement->_ref_banque}})
      {{/if}}
    </td>
    <td>{{mb_value object=$_reglement field=reference}}</td>
    <td>{{mb_value object=$_reglement field=tireur}}</td>
    <td>
      <span onmouseover="ObjectTooltip.createEx(this, '{{$_reglement->_guid}}');">
        {{mb_value object=$_reglement field=montant}}
      </span> 
    </td>
    <td> {{mb_value object=$_reglement field=date format=$conf.date}} </td>
  </tr>
  {{/foreach}}
  <tr>
    <td colspan="5" style="text-align: center;">
      <strong> {{tr}}CReglement-msg-No payment to be collected from the patient{{/tr}} </strong>
    </td>
  </tr>
</table>