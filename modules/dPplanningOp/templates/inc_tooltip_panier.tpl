{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <td class="narrow" style="text-align: center;">
      {{mb_include module=planningOp template=inc_icon_panier with_tooltip=0}}

      {{if $operation->numero_panier}}
      <div>
        {{$operation->numero_panier}}
      </div>
      {{/if}}
    </td>
    <td>
      {{$operation->_legend_panier}}
    </td>
  </tr>

  <tr>
    <td colspan="2">
      {{if $operation->_refs_materiels_operatoires_dm}}
        {{tr}}CMaterielOperatoire-Missing dms{{/tr}} :

        <ul>
          {{foreach from=$operation->_refs_materiels_operatoires_dm item=_materiel_operatoire_dm}}
            <li>
              {{$_materiel_operatoire_dm->_view}}
            </li>
          {{/foreach}}
        </ul>
      {{/if}}

      {{if $operation->_refs_materiels_operatoires_produit}}
        {{tr}}CMaterielOperatoire-Missing products{{/tr}} :

        <ul>
          {{foreach from=$operation->_refs_materiels_operatoires_produit item=_materiel_operatoire_produit}}
            <li>
              {{$_materiel_operatoire_produit->_view}}
            </li>
          {{/foreach}}
        </ul>
      {{/if}}
    </td>
  </tr>
</table>

