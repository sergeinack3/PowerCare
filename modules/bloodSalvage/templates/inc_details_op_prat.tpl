{{*
 * @package Mediboard\BloodSalvage
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<!-- Plages -->
{{foreach from=$praticien->_ref_plages item=_plage}}
  <hr />
  <table class="form">
    <tr>
      <th class="category {{if $vueReduite}}text{{/if}}" colspan="2">
        <a href="?m=bloc&tab=vw_edit_interventions&plageop_id={{$_plage->_id}}" title="Administrer la plage">
          {{$_plage->_ref_salle->_view}}
          {{if $vueReduite}}
            <br />
          {{else}}
            -
          {{/if}}
          {{$_plage->debut|date_format:$conf.datetime}} à
          {{$_plage->fin|date_format:$conf.time}}
        </a>
      </th>
    </tr>
  </table>
  <table class="tbl">
    {{if $_plage->_ref_operations|@count}}
      {{mb_include module=salleOp template=inc_liste_operations urgence=0 operations=$_plage->_ref_operations}}
    {{/if}}

    {{if $_plage->_unordered_operations|@count}}
      <tr>
        <th colspan="10">{{tr}}COperation-back-plageop.not_placed{{/tr}}</th>
      </tr>
      {{mb_include module=salleOp template=inc_liste_operations urgence=0 operations=$_plage->_unordered_operations}}
    {{/if}}
  </table>
{{/foreach}}

<!-- Déplacées -->
{{if $praticien->_ref_deplacees|@count}}
  <hr />
  <table class="form">
    <tr>
      <th class="category" colspan="2">
          {{tr}}COperation-back-plageop.moved{{/tr}}
      </th>
    </tr>
  </table>
  <table class="tbl">
    {{mb_include module=bloodSalvage template=inc_liste_operations urgence=1 operations=$praticien->_ref_deplacees}}
  </table>
{{/if}}

<!-- Urgences -->
{{if $praticien->_ref_urgences|@count}}
  <hr />
  <table class="form">
    <tr>
      <th class="category" colspan="2">
        {{tr}}CSejour.type.urg{{/tr}}
      </th>
    </tr>
  </table>
  <table class="tbl">
    {{mb_include module=bloodSalvage template=inc_liste_operations urgence=1 operations=$praticien->_ref_urgences}}
  </table>
{{/if}}
