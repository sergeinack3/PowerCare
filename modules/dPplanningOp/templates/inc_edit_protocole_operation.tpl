{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function() {
    var form = getForm('editProtocole');

    {{if $protocole->_id}}
    new Url('planningOp', 'ajax_protocole_op_autocomplete')
      .addParam('view_field', '_protocole_op_libelle')
      .addParam('only_validated', 1)
      .autoComplete(
        form._protocole_op_libelle,
        null,
        {
          minChars: 0,
          dropdown: true,
          method: 'get',
          callback: function(input, queryString) {
            return queryString + '&chir_id=' + $V(form.chir_id) + '&function_id=' + $V(form.function_id);
          },
          updateElement: ProtocoleOp.addProtocoleOp
        }
      );
    {{/if}}
  });
</script>

<table class="form">
  <tr>
    <th class="category" colspan="3">
      Informations concernant l'intervention
    </th>
  </tr>

  <tr>
    <th>{{mb_label object=$protocole field="libelle"}}</th>
    <td colspan="2">{{mb_field object=$protocole field="libelle" style="width: 15em"}}</td>
  </tr>

  <tr {{if !$conf.dPplanningOp.COperation.use_ccam}}style="display: none;"{{/if}}>
    <th>
      {{mb_label object=$protocole field="codes_ccam"}}
    </th>
    <td colspan="2">
      <input type="text" name="_codes_ccam" ondblclick="CCAMSelector.init()"  style="width: 12em" value="" />
      <button class="add notext" type="button" onclick="oCcamFieldProtocole.add($V(this.form._codes_ccam), true)">{{tr}}Add{{/tr}}</button>
      <button class="search notext" type="button" onclick="CCAMSelector.init()">{{tr}}button-CCodeCCAM-choix{{/tr}}</button>
      <script>
        Main.add(function() {
          var oForm = getForm("editProtocole");
          new Url("ccam", "autocompleteCcamCodes")
            .autoComplete(oForm._codes_ccam, '', {
              minChars: 1,
              dropdown: true,
              width: "250px",
              updateElement: function(selected) {
                $V(oForm._codes_ccam, selected.down("strong").getText());
                oCcamFieldProtocole.add($V(oForm._codes_ccam), true);
              }
            });
          });

        CCAMSelector.init = function() {
          this.sForm  = "editProtocole";
          this.sView  = "_codes_ccam";
          this.sChir  = "chir_id";
          this.sClass = "_ccam_object_class";
          this.pop();
        }
      </script>
    </td>
  </tr>

  <tr {{if !$conf.dPplanningOp.COperation.use_ccam}}style="display: none;"{{/if}}>
    <th>
      Liste des codes CCAM
      {{mb_field object=$protocole field="codes_ccam" hidden=1}}
    </th>
    <td colspan="2" class="text" id="listCodesCcamProtocole">
    </td>
  </tr>

  <tr {{if !$conf.dPplanningOp.COperation.use_ccam}}style="display: none;"{{/if}}>
    <th>
      Codage CCAM Chir
      {{mb_field object=$protocole field=codage_ccam_chir hidden=true}}
    </th>
    <td id="listCodageCCAM_chir" colspan="2" class="text"></td>
  </tr>

  <tr {{if !$conf.dPplanningOp.COperation.use_ccam}}style="display: none;"{{/if}}>
    <th>
      Codage CCAM Anesth
      {{mb_field object=$protocole field=codage_ccam_anesth hidden=true}}
    </th>
    <td id="listCodageCCAM_anesth" colspan="2" class="text"></td>
  </tr>

  {{if $protocole->_id}}
  <tr>
    <th>{{tr}}COperation-Protocoles op{{/tr}}</th>
    <td colspan="2">
      <input type="text" name="_protocole_op_libelle" />
    </td>
  </tr>
  {{/if}}

  <tr>
    <th>{{tr}}COperation-List of protocoles op{{/tr}}</th>
    <td id="protocoles_op_area" colspan="2" class="text">
      {{mb_include module=planningOp template=inc_list_links_protocoles_op}}
    </td>
  </tr>

  <tr>
    <th>{{mb_label object=$protocole field=exam_extempo}}</th>
    <td colspan="2">{{mb_field object=$protocole field=exam_extempo}}</td>
  </tr>

  <tr>
    <th>{{mb_label object=$protocole field="cote"}}</th>
    <td colspan="2">
      {{mb_field object=$protocole field="cote" style="width: 15em" emptyLabel="Choose"}}
    </td>
  </tr>

  <!-- Choix du type d'anesthésie -->
  {{if $conf.dPplanningOp.COperation.easy_type_anesth}}
    <tr>
      <th>{{mb_label object=$protocole field="type_anesth"}}</th>
      <td colspan="2">
        <select name="type_anesth" style="width: 15em;">
          <option value="">&mdash; Anesthésie</option>
          {{foreach from=$listAnesthType item=curr_anesth}}
            {{if $curr_anesth->actif || ($protocole->type_anesth == $curr_anesth->_id)}}
              <option value="{{$curr_anesth->_id}}" {{if $protocole->type_anesth == $curr_anesth->_id}}selected{{/if}}>
                {{$curr_anesth->name}} {{if !$curr_anesth->actif && $protocole->type_anesth == $curr_anesth->_id}}(Obsolète){{/if}}
              </option>
            {{/if}}
          {{/foreach}}
        </select>
      </td>
    </tr>
  {{/if}}

  <tr>
    <th>{{mb_label object=$protocole field=temp_operation}}</th>
    <td colspan="2">{{mb_field object=$protocole field=temp_operation form=editProtocole class="notNull"}}</td>
  </tr>

  {{if $conf.dPplanningOp.COperation.show_duree_uscpo >= 1}}
    <tr>
      <th>{{mb_label object=$protocole field="duree_uscpo"}}</th>
      <td colspan="2">{{mb_field object=$protocole field="duree_uscpo" increment=true form=editProtocole size="2"}} {{tr}}night{{/tr}}(s)</td>
    </tr>
  {{/if}}

  <tr>
    <th>{{mb_label object=$protocole field=duree_preop}}</th>
    <td colspan="2">{{mb_field object=$protocole field=duree_preop form=editProtocole }}</td>
  </tr>

  <tr>
    <th>{{mb_label object=$protocole field=presence_preop}}</th>
    <td colspan="2">{{mb_field object=$protocole field=presence_preop form=editProtocole }}</td>
  </tr>

  <tr>
    <th>{{mb_label object=$protocole field=presence_postop}}</th>
    <td colspan="2">{{mb_field object=$protocole field=presence_postop form=editProtocole }}</td>
  </tr>

  <tr>
    <th>{{mb_label object=$protocole field=duree_bio_nettoyage}}</th>
    <td colspan="2">{{mb_field object=$protocole field=duree_bio_nettoyage form=editProtocole }}</td>
  </tr>

  <tr>
    <td colspan="3"><hr /></td>
  </tr>

  {{if "dPbloc CPlageOp systeme_materiel"|gconf == "expert"}}
  <tr>
    <td></td>
    <td>
      {{mb_include module=dPbloc template=inc_button_besoins_ressources object_id=$protocole->_id type=protocole_id}}
    </td>
    <td></td>
    {{/if}}

  <tr>
    <td class="text" style="width: 33%;">{{mb_label object=$protocole field="examen"}}</td>
    <td class="text" style="width: 33%;">{{mb_label object=$protocole field="materiel"}}</td>
    <td class="text" style="width: 33%;">{{mb_label object=$protocole field="exam_per_op"}}</td>
  </tr>

  <tr>
    <td>{{mb_field object=$protocole field="examen" rows="3"}}</td>
    <td>{{mb_field object=$protocole field="materiel" rows="3"}}</td>
    <td>{{mb_field object=$protocole field="exam_per_op" rows="3"}}</td>
  </tr>

  <tr>
    <td class="text">{{mb_label object=$protocole field="depassement"}}</td>
    <td class="text">{{mb_label object=$protocole field="forfait"}}</td>
    <td class="text">{{mb_label object=$protocole field="fournitures"}}</td>
  </tr>

  <tr>
    <td>{{mb_field object=$protocole field="depassement" size="4"}}</td>
    <td>{{mb_field object=$protocole field="forfait" size="4"}}</td>
    <td>{{mb_field object=$protocole field="fournitures" size="4"}}</td>
  </tr>
  <tr>
    <td colspan="3">{{mb_label object=$protocole field="rques_operation"}}
      {{mb_field object=$protocole field="rques_operation"}}
    </td>
  </tr>

  {{if $protocole->_id}}
  <tr>
    <th></th>
    <td colspan="2">
      {{mb_include module=files template=inc_button_add_docitems context=$protocole type=operation}}
    </td>
  </tr>
  {{/if}}
</table>
