{{*
 * @package Mediboard\GestionCab
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main">
  <tr>
    <!-- Rubrique --> 
    <td class="halfPane" rowspan="3">
      <a class="button new" href="?m=gestionCab&tab=configure&facture_id=0">
        Créer une nouvelle rubrique
      </a>
      <table class="tbl">
        <tr>
          <th class="title" colspan="2">Rubrique</th>
        </tr>
         <tr>
          <th class="title">{{$etablissement}}</th>
        </tr>
        <tr>
          <th>Libellé</th>
        </tr>
        {{foreach from=$listRubriqueGroup item=_item}}
        <tr {{if $_item->_id == $rubrique->_id}}class="selected"{{/if}}>
          <td>
           <a href="?m=gestionCab&tab=edit_rubrique&rubrique_id={{$_item->_id}}" title="Modifier la rubrique">
              {{mb_value object=$_item field="nom"}}
            </a>
          </td>
        </tr>
        {{/foreach}}
        {{foreach from=$listRubriqueFonction key=keyRubrique item=_itemRubrique}}
        {{if $_itemRubrique|@count}}
        <tr>
          <th class="title">{{$keyRubrique}}</th>
        </tr>
         <tr>
          <th>Libellé</th>
        </tr>
        {{foreach from=$_itemRubrique item=_item}}
        <tr {{if $_item->_id == $rubrique->_id}}class="selected"{{/if}}>
          <td>
           <a href="?m=gestionCab&tab=edit_rubrique&rubrique_id={{$_item->_id}}" title="Modifier la rubrique">
              {{mb_value object=$_item field="nom"}}
            </a>
           </td>
        </tr>
        {{/foreach}}
        {{/if}}
        {{/foreach}}
      </table>
    </td>

    <!-- Opération sur les rubriques -->
    <td class="halfPane">
      <form name="editFrm" action="?m={{$m}}" method="post" onsubmit="return checkForm(this)">
        {{mb_class object=$rubrique}}
        {{mb_key   object=$rubrique}}
        <input type="hidden" name="del" value="0" />
        <table class="form">
          {{mb_include module=system template=inc_form_table_header object=$rubrique}}

          <tr>
            <th>{{mb_label object=$rubrique field="nom"}}</th>
            <td>{{mb_field object=$rubrique field="nom"}} </td>
          </tr>
          <tr>
            <th>{{mb_label object=$rubrique field="function_id"}}</th>
            <td>
              <select name="function_id">
                <option value="">&mdash; Associer à une fonction &mdash;</option>
                {{mb_include module=mediusers template=inc_options_function list=$listFunc selected=$rubrique->function_id}}
             </select>
            </td>
          </tr>
          <tr>
            <td class="button" colspan="2">
              <button class="submit">{{tr}}Validate{{/tr}}</button>
              {{if $rubrique->_id}}
                <button class="trash" type="button" onclick="confirmDeletion(this.form, {typeName: 'la rubrique', objName: '{{$rubrique->_view|smarty:nodefaults|JSAttribute}}'})">{{tr}}Delete{{/tr}}</button>
              {{/if}}
            </td>
          </tr>
        </table>
      </form>
    </td>
  </tr>
</table>