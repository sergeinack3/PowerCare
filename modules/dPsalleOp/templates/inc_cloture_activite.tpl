{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $object|instanceof:'Ox\Mediboard\PlanningOp\CSejour'}}
  {{assign var=chir value=$object->_ref_praticien}}
{{/if}}
{{if $object|instanceof:'Ox\Mediboard\PlanningOp\COperation'}}
  {{assign var=chir value=$object->_ref_chir}}
{{/if}}
 
<script type="text/javascript">
  reloadAfterCloture = function() {
    ActesCCAM.refreshList('{{$object->_id}}', '{{$chir->_id}}');
    getForm('clotureFormActes').up('div').up().down('button.change').onclick();
  }
</script>

<form name="clotureFormActes" method="post"
  onsubmit="return onSubmitFormAjax(this, {onComplete: reloadAfterCloture})">
  <input type="hidden" name="m" value="dPsalleOp" />
  <input type="hidden" name="object_id" value="{{$object->_id}}" /> 
  <input type="hidden" name="object_class" value="{{$object->_class}}" />
  <input type="hidden" name="dosql" value="do_cloture_activite_aed" />
  <input type="hidden" name="chir_id" value="{{$chir->_id}}" />
  {{if $anesth->_id}}
    <input type="hidden" name="anesth_id" value="{{$anesth->_id}}" />
  {{/if}}
  
  <table class="form">
    <tr>
      <th colspan="2" class="title">
        {{$object}}
      </th>
    </tr>
    <tr>
      <th colspan="2" class="category">Activité 1</th>
    </tr>
    {{if $non_signes_activite_1 > 0}}
      <tr>
        <td colspan="2">
          <div class="small-warning">Il y a {{$non_signes_activite_1}} acte(s) non signés qui empêche(nt) la clôture de l'activité 1.</div>
        </td>
      </tr>
    {{elseif $object->cloture_activite_1}}
      <tr>
        <td colspan="2" class="empty">
          Activité clôturée.
        </td>
      </tr>
    {{else}}
      <tr>
        <td>
          {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$chir}}
        </td>
        <td>
          <input type="password" class="str" name="password_activite_1" />
          <button type="button" onclick="this.form.onsubmit()" class="save">Clôturer</button>
        </td>
      </tr>
    {{/if}}
    {{if $anesth->_id}}
      <tr>
        <th colspan="2" class="category">Activité 4</th>
      </tr>
      {{if $non_signes_activite_4 > 0}}
        <tr>
          <td colspan="2">
            <div class="small-warning">Il y a {{$non_signes_activite_4}} acte(s) non signés qui empêche(nt) la clôture de l'activité 1.</div>
          </td>
        </tr>
      {{elseif $object->cloture_activite_4}}
        <tr>
          <td colspan="2" class="empty">
            Activité clôturée.
          </td>
        </tr>
      {{else}}
        <tr>
          <td>
            {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$anesth}}
          </td>
          <td>
            <input type="password" class="str" name="password_activite_4" />
            <button type="button" onclick="this.form.onsubmit()" class="save">Clôturer</button>
          </td>
        </tr>
      {{/if}}
    {{/if}}
  </table>
</form>
