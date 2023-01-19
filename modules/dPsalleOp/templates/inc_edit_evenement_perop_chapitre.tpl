{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    GestePerop.categoriesPeropAutocomplete(getForm('edit-chapitre-{{$evenement_chapitre->_guid}}'), getForm('categoriePerop'), '{{$evenement_chapitre->_guid}}');
  });
</script>

<form name="categoriePerop" action="?" target="#" method="post"
      onsubmit="onSubmitFormAjax(this, Control.Modal.refresh);">
  {{mb_key   object=$categorie_perop}}
  {{mb_class object=$categorie_perop}}

  <input type="hidden" name="chapitre_id" value="{{$evenement_chapitre->_id}}" />
</form>

<form name="edit-chapitre-{{$evenement_chapitre->_guid}}" method="post" action="" onsubmit="return onSubmitFormAjax(this, Control.Modal.close);">
  <input type="hidden" name="m" value="dPsalleOp" />
  {{mb_class object=$evenement_chapitre}}
  {{mb_key object=$evenement_chapitre}}

  {{mb_field object=$evenement_chapitre field=group_id hidden=true}}

  <table class="main form">
    {{mb_include module=system template=inc_form_table_header object=$evenement_chapitre}}

      <tr>
        <th>{{mb_label object=$evenement_chapitre field=libelle}}</th>
        <td>{{mb_field object=$evenement_chapitre field=libelle}}</td>
      </tr>
      <tr>
        <th>{{mb_label object=$evenement_chapitre field=description}}</th>
        <td>{{mb_field object=$evenement_chapitre field=description}}</td>
      </tr>

      {{if $evenement_chapitre->_id}}
        <tr>
          <th title="{{tr}}CAnesthPeropChapitre-Associate a Perop category with a chapter-desc{{/tr}}">{{tr}}CGestePerop-categorie_id-court{{/tr}}</th>
          <td>
            <input type="text" name="categorie_perop_id_view" value="" />
          </td>
        </tr>
      {{/if}}

      <tr>
        <th>{{mb_label object=$evenement_chapitre field=actif}}</th>
        <td>{{mb_field object=$evenement_chapitre field=actif}}</td>
      </tr>

    {{mb_include module=system template=inc_form_table_footer object=$evenement_chapitre options_ajax="Control.Modal.close"}}
  </table>
</form>

{{if $evenement_chapitre->_id}}
  <br>
  <br>
  <table class="layout main">
    <tr>
      <td>
          {{assign var=categories_perop value=$evenement_chapitre->_ref_anesth_categories_perop}}
        <table class="tbl">
          <tr>
            <th class="title" colspan="3">{{tr}}CAnesthPeropChapitre-Associated Perop Categories{{/tr}} ({{$categories_perop|@count}})</th>
          </tr>
          <tr>
            <th>{{mb_title class=CGestePerop field=libelle}}</th>
            <th class="narrow">{{tr}}common-Action{{/tr}}</th>
          </tr>
          {{foreach from=$categories_perop item=_categorie_perop}}
            <tr>
              <td>
                <span onmouseover="ObjectTooltip.createEx(this, '{{$_categorie_perop->_guid}}')">
                  {{$_categorie_perop->_view}}
                </span>
              </td>
              <td class="button">
                <form name="editFile{{$_categorie_perop->_id}}" action="?" target="#" method="post">
                    {{mb_key   object=$_categorie_perop}}
                    {{mb_class object=$_categorie_perop}}
                  <input type="hidden" name="categorie_id" value="" />
                  <button type="button" onclick="GestePerop.confirmDissociateElement(this.form, 'categorie');"
                          title="{{tr}}CAnesthPeropChapitre-action-Dissociate the Perop category from the chapter{{/tr}}">
                    <i class="fas fa-unlink"></i>
                  </button>
                </form>
              </td>
            </tr>
          {{foreachelse}}
            <tr>
              <td class="empty" colspan="3">
                {{tr}}CGestePerop.none{{/tr}}
              </td>
            </tr>
          {{/foreach}}
        </table>
      </td>
    </tr>
  </table>
{{/if}}
