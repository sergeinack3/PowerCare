{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$object->_can->read}}
  <div class="small-info">
    {{tr}}{{$object->_class}}{{/tr}} : {{tr}}access-forbidden{{/tr}}
  </div>
  {{mb_return}}
{{/if}}

{{mb_script module=planningOp script=prestations ajax=1}}
{{mb_script module=hospi script=affectation ajax=1}}
{{assign var=sejour       value=$object->_ref_sejour}}
{{assign var=patient      value=$sejour->_ref_patient}}
{{assign var=operations   value=$sejour->_ref_operations}}
{{assign var=affectations value=$sejour->_ref_affectations}}

<table class="tbl">
  {{if $object->sejour_id}}
    {{mb_include module=planningOp template=inc_sejour_affectation_view}}
  {{else}}
    <tr>
      <th>
        Lit bloqué {{mb_include module=system template=inc_interval_datetime from=$object->entree to=$object->sortie}}
      </th>
    </tr>
  {{/if}}
</table>

{{if $object->_can->edit}}
  <!-- Formulaire de suppression d'affectation -->
  <form name="delAffect_{{$object->_id}}" method="post">
    <input type="hidden" name="m" value="hospi" />
    <input type="hidden" name="dosql" value="do_affectation_aed" />
    <input type="hidden" name="del" value="1" />
    {{mb_key object=$object}}
  </form>
  <table class="tbl">
    <tr>
      <td class="button">
        <button type="button" class="edit"
                onclick="this.up('div').hide(); Affectation.edit('{{$object->_id}}');">{{tr}}Edit{{/tr}}</button>
        <button type="button" class="cancel"
                onclick="Affectation.delAffectation(getForm('delAffect_{{$object->_id}}'), '{{$object->lit_id}}', 'CSejour-{{$sejour->_id}}');">{{tr}}Delete{{/tr}}</button>
        {{if $sejour->_id && "dPhospi prestations systeme_prestations"|gconf == "expert"}}
          <button type="button" class="search" onclick="Prestations.edit('{{$sejour->_id}}');">Prestations</button>
        {{/if}}
      </td>
    </tr>
  </table>
{{/if}}