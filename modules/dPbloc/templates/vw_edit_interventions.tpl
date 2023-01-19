{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=bloc       script=edit_planning ajax=1}}
{{mb_script module=planningOp script=operation     ajax=1}}
{{mb_script module=salleOp    script=salleOp       ajax=1}}

<script>
  printFicheAnesth = function(dossier_anesth_id, operation_id) {
    var url = new Url("cabinet", "print_fiche"); 
    url.addParam("dossier_anesth_id", dossier_anesth_id);
    url.addParam("operation_id", operation_id);
    url.popup(700, 500, "printFicheAnesth");
  };

  chooseAnesthCallback = function() {
    location.reload(); 
  };
  
  var reloadAllLists = function() {
    reloadLeftList();
    reloadRightList();
  };
  
  reloadLeftList = function() {
    var url = new Url("bloc", "ajax_list_intervs");
    url.addParam("plageop_id", {{$plage->_id}});
    url.addParam("list_type" , "left");
    url.requestUpdate("left_list");
  };
  
  reloadRightList = function() {
    var url = new Url("bloc", "ajax_list_intervs");
    url.addParam("plageop_id", {{$plage->_id}});
    url.addParam("list_type" , "right");
    url.requestUpdate("right_list");
  };
  
  submitOrder = function(oForm, side) {
    var callback = function() {
      reloadAllLists();
      if (window.MultiSalle) {
        MultiSalle.reloadOpsPlanning();
      }
    };

    if (side == "left") {
      callback = function() {
        reloadLeftList();
        if (window.MultiSalle) {
          MultiSalle.reloadOps();
        }
      };
    }
    if (side == "right") {
      callback = function() {
        reloadRightList();
        if (window.MultiSalle) {
          MultiSalle.reloadPlanning();
        }
      };
    }

    return onSubmitFormAjax(oForm, callback);
  };

  extraInterv = function(op_id) {
    var url = new Url("bloc", "ajax_edit_extra_interv");
    url.addParam("op_id", op_id);
    url.requestModal(700, 400, {onClose: function() {
      reloadAllLists();
      if (window.MultiSalle) {
        MultiSalle.reloadOps();
      }
    }});
  };
  
  reloadModifPlage = function() {
    var url = new Url("bloc", "ajax_modif_plage");
    url.addParam("plageop_id", {{$plage->_id}});
    url.requestUpdate("modif_plage", function() {
      reloadAllLists();
      if (window.MultiSalle) {
        MultiSalle.reloadOps();
      }
    });
  };
  
  reloadPersonnelPrevu = function() {
    var url = new Url("bloc", "ajax_view_personnel_plage");
    url.addParam("plageop_id", {{$plage->_id}});
    url.requestUpdate("personnel_en_salle");
  };

  multiSalle = function(salles_ids) {
    var url = new Url("bloc", "vw_multi_salle");
    url.addParam("salles_ids[]", salles_ids, true);
    url.addParam("date", "{{$plage->date}}");
    url.addParam("chir_id", "{{$plage->chir_id}}");
    url.requestModal("100%", "100%", {onClose: reloadAllLists});
  };

  Main.add(function() {
    reloadModifPlage();
    reloadPersonnelPrevu();
  });

</script>

<form name="toggleRankOp" method="post">
  <input type="hidden" name="m" value="planningOp" />
  <input type="hidden" name="dosql" value="do_planning_aed" />
  <input type="hidden" name="operation_id" />
  <input type="hidden" name="rank" />
  <input type="hidden" name="_move" value="toggle" />
</form>

<table class="main">
  <tr>
    <th class="title" colspan="2">
      {{mb_include module=system template=inc_object_notes object=$plage}}
      {{mb_include module=system template=inc_object_idsante400 object=$plage}}
      {{mb_include module=system template=inc_object_history object=$plage}}

      {{if $multi_salle|@count > 1}}
        <button type="button" class="search" style="float: left;" onclick="multiSalle({{$multi_salle|@json|JSAttribute}})">Gestion multi-salle</button>
      {{/if}}

      {{if $plage->chir_id}}
        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$plage->_ref_chir}}
      {{else}}
        {{$plage->_ref_spec}}
      {{/if}}
      - {{$plage->date|date_format:$conf.longdate}}
      - {{$plage->_ref_salle->nom}}
    </th>
  </tr>
  <tr>
    <td>
      <table class="form">
        <tr>
          <td>
            <div id="modif_plage">
            </div>
            <div class="small-info">Pour plus de simplicité, l'ajout de personnel se fait maintenant directement dans la case de droite.</div>
          </td>
        </tr>
      </table>   
    </td>
    <td id="personnel_en_salle">
    </td> 
  </tr>
  <tr>
    <td class="halfPane" id="left_list">
    </td>
    <td class="halfPane" id="right_list">
    </td>
  </tr>
</table>