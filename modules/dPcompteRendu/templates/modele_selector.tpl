{{*
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$praticien->_can->edit}}
  <div class="big-info">
    {{tr}}CCompteRendu-access_denied_docs_user{{/tr}}
  </div>
  {{mb_return}}
{{/if}}

<script>
  setClose = function(modele_id, object_id, fast_edit) {
    if (window.opener) {
      var oSelector = window.opener.modeleSelector[{{$target_id}}];
      oSelector.set(modele_id, object_id, fast_edit);
    }
    window.close();
  };

  selectModele = function(object_id, order_id) {
    new Url('appFineClient', 'ajax_store_compte_rendu_order')
      .addParam('order_id' , order_id)
      .addParam('object_id', object_id)
      .addParam('object_class', 'CCompteRendu')
      .requestUpdate('systemMsg', Control.Modal.close);
  };

  Main.add(function() {
    Control.Tabs.create("tabs-modeles");

    var form = getForm('addConsFrm');
    new Url('mediusers', 'ajax_users_autocomplete')
      .addParam('edit', '1')
      .addParam('input_field', 'user_id_view')
      .autoComplete(form.user_id_view, null,
        {
          minChars: 0,
          method: 'get',
          select: 'view',
          dropdown: true,
          afterUpdateElement: function(field, selected) {
            $V(form.praticien_id, selected.get('id'));
          }
        }
      );
  });
</script>

<h2>{{tr var1=$praticien}}CCompteRendu-docs_for{{/tr}} ({{tr}}{{$target_class}}{{/tr}})</h2>

<!-- Choix du praticien -->
{{if !$appfine}}
<form name="addConsFrm" method="get" class="prepared">
  <input type="hidden" name="m" value="{{$m}}" />
  <input type="hidden" name="a" value="{{$a}}" />
  <input type="hidden" name="dialog" value="{{$dialog}}" />
  <input type="hidden" name="object_id" value="{{$target_id}}" />
  <input type="hidden" name="object_class" value="{{$target_class}}" />

  <label for="praticien_id" title="Choisir un autre utilisateur permet d'accéder à ses modèles">
    Changer d'utilisateur
  </label> :

  <input type="hidden" name="praticien_id" value="{{$praticien->_id}}" onchange="this.form.submit();"/>
  <input type="text" name="user_id_view" value="{{$praticien->_ref_user}}" class="autocomplete" />
</form>
{{/if}}

<ul id="tabs-modeles" class="control_tabs">
{{foreach from=$modelesCompat key=class item=modeles}}
  {{assign var=count_modeles value=0}}
  {{foreach from=$modeles item=_modeles}}
    {{math equation=x+y x=$count_modeles y=$_modeles|@count assign=count_modeles}}
  {{/foreach}}
  <li><a href="#{{$class}}" {{if !$count_modeles}}class="empty"{{/if}}>{{tr}}{{$class}}{{/tr}}</a></li>
{{/foreach}}
{{foreach from=$modelesNonCompat key=class item=modeles}}
  {{assign var=count_modeles value=0}}
  {{foreach from=$modeles item=_modeles}}
    {{math equation=x+y x=$count_modeles y=$_modeles|@count assign=count_modeles}}
  {{/foreach}}
  <li><a href="#{{$class}}" class="{{if !$count_modeles}}empty{{else}}wrong{{/if}}">{{tr}}{{$class}}{{/tr}}</a></li>
{{/foreach}}
</ul>

{{foreach from=$modelesCompat key=class item=modeles}}
  {{mb_include template=inc_vw_list_models}}
{{/foreach}}

{{foreach from=$modelesNonCompat key=class item=modeles}}
  {{mb_include template=inc_vw_list_models}}
{{/foreach}}

<div class="small-info">
  {{tr}}CCompteRendu-click_doc_to_use{{/tr}}
  <br />
  <span class="wrong" style="width: 50px; display: inline-block; margin-bottom: 1px;">&nbsp;</span> {{tr}}CCompteRendu-sections_maybe_conflicting{{/tr}}.<br />
  <span class="empty" style="width: 50px; display: inline-block;">&nbsp;</span> {{tr}}CCompteRendu-sections_empty{{/tr}}.
</div>