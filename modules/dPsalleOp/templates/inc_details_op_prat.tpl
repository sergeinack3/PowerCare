{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{foreach from=$praticien->_ref_plages item=_plage}}
<hr />

  <table class="form">
    <tr>
      <th class="category{{if $vueReduite}} text{{/if}}" colspan="2">
        {{mb_include module=system template=inc_object_notes object=$_plage}}
        <a class="me-color-black-high-emphasis" onclick="EditPlanning.order('{{$_plage->_id}}');" href="#" title="Agencer les interventions">
          {{$_plage->_ref_salle->_view}}
          {{if $vueReduite}}
            <br />
          {{else}}
            -
          {{/if}}
          {{$_plage->debut|date_format:$conf.time}} à {{$_plage->fin|date_format:$conf.time}}
        </a>
      </th>
    </tr>
  </table>

  <table class="tbl">
    {{if $_plage->_ref_operations|@count}}
      {{mb_include module="salleOp" template="inc_liste_operations" urgence=0 operations=$_plage->_ref_operations ajax_salle=1}}
    {{/if}}

    {{if $_plage->_unordered_operations|@count}}
      <tr>
        <th colspan="10">Non placées</th>
      </tr>
      {{mb_include module="salleOp" template="inc_liste_operations" urgence=0 operations=$_plage->_unordered_operations ajax_salle=1}}
    {{/if}}
  </table>
{{/foreach}}

<!-- Déplacées -->
{{if $praticien->_ref_deplacees|@count}}
  <hr />

  <table class="form">
    <tr>
      <th class="category" colspan="2">
        Déplacées
      </th>
    </tr>
  </table>

  <table class="tbl">
    {{mb_include module="salleOp" template="inc_liste_operations" urgence=1 operations=$praticien->_ref_deplacees ajax_salle=1}}
  </table>
{{/if}}

<!-- Urgences -->
{{if $praticien->_ref_urgences|@count}}
  <hr />

  <table class="form">
    <tr>
      <th class="category" colspan="2">
        Hors plage
      </th>
    </tr>
  </table>

  <table class="tbl">
    {{mb_include module="salleOp" template="inc_liste_operations" urgence=1 operations=$praticien->_ref_urgences ajax_salle=1}}
  </table>
{{/if}}