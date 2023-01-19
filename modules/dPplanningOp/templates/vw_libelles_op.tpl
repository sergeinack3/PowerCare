{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$operation_id}}
  <div class="small-info">Veuillez créer l'intervention avant de pouvoir ajouter des libellés</div>
  {{mb_return}}
{{/if}}

{{assign var=position value=1}}

<table class="form">
  <tr>
    <th colspan="2" class="category">
      Choix des libellés
    </th>
  </tr>
  {{foreach from=$liaisons item=_liaison}}
    {{assign var=liaison_id value=$_liaison->_id}}
    {{assign var=position value=$_liaison->numero}}
    <tr>
      <th style="height: 25px;">
        Libellé {{$_liaison->numero}}
      </th>
      <td>
        <form name="create-CLiaisonLibelleInterv{{$liaison_id}}" action="#" method="post" onsubmit="return LiaisonOp.submit(this);">
          {{mb_key    object=$_liaison}}
          {{mb_class  object=$_liaison}}
          {{assign var=libelle_save value=""}}
          {{if $_liaison->libelleop_id}}
            {{assign var=libelle_save value=$_liaison->_ref_libelle->nom}}
          {{/if}}
          <input type="hidden" name="_libelle_save" value="{{$libelle_save}}" />
          <input type="hidden" name="del" value="0" />
          <input type="hidden" name="operation_id" value="{{$_liaison->operation_id}}" />
          <input type="hidden" name="numero" value="{{$_liaison->numero}}" />
          <input type="hidden" name="group_id" value="{{$g}}" />
          {{mb_field object=$_liaison field="libelleop_id" canNull=false form="create-CLiaisonLibelleInterv$liaison_id" autocomplete="true,1,50,true,true"
              style="width:300px;" onchange="return LiaisonOp.submit(this.form);" placeholder="Choisir un libellé"  onclick="\$V(this, '');"
              onblur="\$V(this, this.form.libelle_save.value);"}}
          {{if $_liaison->_id}}
            <button class="cancel notext" type="button" onclick="this.form.del.value = 1;LiaisonOp.onDeletion(this.form);">Supprimer le libellé</button>
          {{/if}}
        </form>
      </td>
    </tr>
  {{/foreach}}
  <!-- new libelle -->
  {{math assign=position equation="a+1" a=$position}}
    <tr>
      <th>Nouveau Libellé</th>
      <td>
        <form name="create-CLiaisonLibelleInterv" action="#" method="post" onsubmit="return LiaisonOp.submit(this);">
          {{mb_key    object=$liaison}}
          {{mb_class  object=$liaison}}
          {{assign var=libelle_save value=""}}
          {{if $liaison->libelleop_id}}
            {{assign var=libelle_save value=$liaison->_ref_libelle->nom}}
          {{/if}}
          <input type="hidden" name="_libelle_save" value="{{$libelle_save}}" />
          <input type="hidden" name="del" value="0" />
          <input type="hidden" name="operation_id" value="{{$liaison->operation_id}}" />
          <input type="hidden" name="numero" value="{{$position}}" />
          <input type="hidden" name="group_id" value="{{$g}}" />
          {{mb_field object=$liaison field="libelleop_id" canNull=false form="create-CLiaisonLibelleInterv" autocomplete="true,1,50,true,true"
          style="width:300px;" onchange="return LiaisonOp.submit(this.form);" placeholder="Choisir un libellé"  onclick="\$V(this, '');"
          onblur="\$V(this, this.form._libelle_save.value);"}}
          {{if $liaison->_id}}
            <button class="cancel notext" type="button" onclick="this.form.del.value = 1;LiaisonOp.onDeletion(this.form);">Supprimer le libellé</button>
          {{/if}}
        </form>
      </td>
    </tr>
</table>