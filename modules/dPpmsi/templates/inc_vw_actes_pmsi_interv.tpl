{{*
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_include module=pmsi template=inc_header_actes subject=$operation}}
{{mb_include module=pmsi template=inc_codage_actes subject=$operation}}

<table class="main layout">
  {{if ($conf.dPpmsi.systeme_facturation == "siemens")}}
    <tr>
      <td colspan="4">
        <form name="editOpFrm{{$operation->_id}}" action="?" method="post" onsubmit="return onSubmitFormAjax(this)">
          <input type="hidden" name="dosql" value="do_planning_aed" />
          <input type="hidden" name="m" value="dPplanningOp" />
          <input type="hidden" name="del" value="0" />
          <input type="hidden" name="operation_id" value="{{$operation->operation_id}}" />
          <table class="form">
            <tr>
              <th class="category" colspan="2">
                <em>Lien S@nté.com</em> : Intervention
              </th>
            </tr>
            <tr>
              <th><label for="_cmca_uf_preselection" title="Choisir une pré-selection pour remplir les unités fonctionnelles">Pré-sélection</label></th>
              <td>
                <select name="_cmca_uf_preselection" onchange="PMSI.choosePreselection(this)">
                  <option value="">&mdash; Choisir une pré-selection</option>
                  <option value="ABS|ABSENT">(ABS) Absent</option>
                  <option value="AEC|ARRONDI EURO">(AEC) Arrondi Euro</option>
                  <option value="AEH|ARRONDI EURO">(AEH) Arrondi Euro</option>
                  <option value="AMB|CHIRURGIE AMBULATOIRE">(AMB) Chirurgie Ambulatoire</option>
                  <option value="CHI|CHIRURGIE">(CHI) Chirurgie</option>
                  <option value="CHO|CHIRURGIE COUTEUSE">(CHO) Chirurgie Coûteuse</option>
                  <option value="EST|ESTHETIQUE">(EST) Esthétique</option>
                  <option value="EXL|EXL POUR RECUP V4 V5">(EXL) EXL pour récup. v4 v5</option>
                  <option value="EXT|EXTERNES">(EXT) Externes</option>
                  <option value="MED|MEDECINE">(MED) Médecine</option>
                  <option value="PNE|PNEUMOLOGUE">(PNE) Pneumologie</option>
                  <option value="TRF|TRANSFERT >48H">(TRF) Transfert > 48h</option>
                  <option value="TRI|TRANSFERT >48H">(TRI) Transfert > 48h</option>
                </select>
              </td>
            </tr>
            <tr>
              <th>
                <label for="code_uf" title="Choisir un code pour l'unité fonctionnelle">Code d'unité fonct.</label>
              </th>
              <td>
                <input type="text" class="notNull {{$operation->_props.code_uf}}" name="code_uf" value="{{$operation->code_uf}}" size="10" maxlength="10" />
              </td>
            </tr>
            <tr>
              <th>
                <label for="libelle_uf" title="Choisir un libellé pour l'unité fonctionnelle">Libellé d'unité fonct.</label>
              </th>
              <td>
                <input type="text" class="notNull {{$operation->_props.libelle_uf}}" name="libelle_uf" value="{{$operation->libelle_uf}}" size="20" maxlength="35" onchange="this.form.onsubmit()" />
              </td>
            </tr>
            <tr>
              <td colspan="2" id="updateOp{{$operation->operation_id}}"></td>
            </tr>
          </table>
        </form>
      </td>
    </tr>
  {{/if}}
  <tr>
    <td colspan="4" id="export_{{$operation->_class}}_{{$operation->_id}}">
    </td>
  </tr>
</table>