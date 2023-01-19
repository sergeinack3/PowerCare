{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=patient value=$_operation->_ref_patient}}

<td>
  {{if $_operation->_status_panier !== "complete"}}
    <input type="checkbox" class="panier_op" value="{{$_operation->_id}}" {{if "dPsalleOp COperation numero_panier_mandatory"|gconf && !$_operation->numero_panier}}disabled{{/if}} />
  {{/if}}
</td>
<td>
  <button class="search notext me-primary" onclick="PreparationSalle.showPanier('{{$_operation->_id}}');"></button>
</td>
<td>
  <span onmouseover="ObjectTooltip.createEx(this, '{{$patient->_guid}}');">
    {{$patient->_view}}
  </span>
</td>
<td>
  <span onmouseover="ObjectTooltip.createEx(this, '{{$_operation->_guid}}');">
    {{$_operation->libelle}}
  </span>
</td>
<td>
  {{$_operation->_ref_salle->_view}}
</td>
<td>
  {{foreach from=$_operation->_ref_protocoles_operatoires item=_protocole_operatoire}}
    <div>
      {{$_protocole_operatoire->_view}}
    </div>
  {{foreachelse}}
    <div class="empty">
      {{tr}}CProtocoleOperatoire.none{{/tr}}
    </div>
  {{/foreach}}
</td>
<td style="text-align: center;">
  {{mb_include module=planningOp template=inc_icon_panier operation=$_operation}}

  <div class="me-margin-top-4">
    <form name="editNoPanier{{$_operation->_id}}" method="post" onsubmit="return onSubmitFormAjax(this, PreparationSalle.toggleCheckBoxLine.curry(this).bind(this));">
      {{mb_class object=$_operation}}
      {{mb_key   object=$_operation}}
      {{me_form_field mb_object=$_operation mb_field=numero_panier}}
        {{mb_field object=$_operation field=numero_panier onchange="this.form.onsubmit();"}}
      {{/me_form_field}}
    </form>
  </div>

  {{if $operation->_filter_panier === "missing"}}
    <div style="text-align: left;">
      {{if $_operation->_refs_materiels_operatoires_dm|@count}}
        {{tr}}CMaterielOperatoire-Missing dms{{/tr}} :
        <ul>
          {{foreach from=$_operation->_refs_materiels_operatoires_dm item=_materiel_operatoire}}
            <li>
              {{$_materiel_operatoire->_view}}
              <div class="compact">
                {{$_materiel_operatoire->_ref_dm->_ref_location->name}}
              </div>
            </li>
          {{/foreach}}
        </ul>
      {{/if}}

      {{if $_operation->_refs_materiels_operatoires_produit|@count}}
        {{tr}}CMaterielOperatoire-Missing products{{/tr}} :
        <ul>
          {{foreach from=$_operation->_refs_materiels_operatoires_produit item=_materiel_operatoire}}
            <li>{{$_materiel_operatoire->_view}}</li>
          {{/foreach}}
        </ul>
      {{/if}}
    </div>
  {{/if}}
</td>
