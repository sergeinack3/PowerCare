{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl" style="width: 99.5%">
  <tr class="clear">
    <th colspan="4">
      <h1>
        <button type="button" class="print not-printable" style="float: right;" onclick="window.print();">{{tr}}Print{{/tr}}</button>

        {{tr var1=$date_min|date_format:$conf.date var2=$date_max|date_format:$conf.date var3=$planning->total var4=$planning->total_horsplage}}Bloc-Preparation for dates{{/tr}}
      </h1>
    </th>
  </tr>
  {{foreach from=$planning->salles item=_salle}}
    <tr>
      <th colspan="4">
        <h2>
          {{if $_salle->_id}}
            {{tr}}CSalle{{/tr}} {{$_salle->_view}}
          {{else}}
            {{tr}}COperation-urgences{{/tr}}
          {{/if}}
        </h2>
      </th>
    </tr>
    <tr>
      <th class="narrow">
        {{tr}}Hour{{/tr}}
      </th>
      <th>
        {{tr}}CPatient{{/tr}}
      </th>
      <th>
        {{tr}}COperation{{/tr}}
      </th>
      <th class="halfPane">
        {{tr}}CMaterielOperatoire{{/tr}}
      </th>
    </tr>

    {{foreach from=$_salle->_ref_operations item=_operation}}
      <tr>
        <td>
          {{if $_operation->rank}}
            {{$_operation->time_operation|date_format:$conf.time}}
          {{else}}
            NP
          {{/if}}
        </td>
        <td>
          {{$_operation->_ref_patient->_view}}
        </td>
        <td>
          {{$_operation->libelle}}
        </td>
        <td>
          {{if $_operation->_refs_materiels_operatoires_dm|@count}}
            {{tr}}CMaterielOperatoire-DM usage unique{{/tr}} :
            <ul>
              {{foreach from=$_operation->_refs_materiels_operatoires_dm item=_materiel_operatoire}}
                <li>
                  {{$_materiel_operatoire->qte_prevue}} {{$_materiel_operatoire->_view}}
                </li>
              {{/foreach}}
            </ul>
          {{/if}}

          {{if $_operation->_refs_materiels_operatoires_dm_sterilisables|@count}}
            {{tr}}CMaterielOperatoire-DM sterilisables{{/tr}} :
            <ul>
              {{foreach from=$_operation->_refs_materiels_operatoires_dm_sterilisables item=_materiel_operatoire}}
                <li>
                  {{$_materiel_operatoire->qte_prevue}} {{$_materiel_operatoire->_view}}
                </li>
              {{/foreach}}
            </ul>
          {{/if}}

          {{if $_operation->_refs_materiels_operatoires_produit|@count}}
            {{tr}}CMaterielOperatoire-code_cip{{/tr}} :
            <ul>
              {{foreach from=$_operation->_refs_materiels_operatoires_produit item=_materiel_operatoire}}
                <li>
                  {{$_materiel_operatoire->qte_prevue}} {{$_materiel_operatoire->_view}}
                </li>
              {{/foreach}}
            </ul>
          {{/if}}
        </td>
      </tr>
    {{/foreach}}

  {{foreachelse}}
  <tr>
    <td class="empty">{{tr}}COperation.none{{/tr}}</td>
  </tr>
  {{/foreach}}
</table>
