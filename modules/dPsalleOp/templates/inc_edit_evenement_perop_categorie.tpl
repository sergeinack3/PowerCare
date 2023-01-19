{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    GestePerop.gestePeropAutocomplete(getForm('edit-evenement-{{$evenement_categorie->_guid}}'), getForm('gestePerop'), 'category', '{{$evenement_categorie->_guid}}');
  });
</script>

{{assign var=file value=$evenement_categorie->_ref_file}}

<form name="gestePerop" action="?" target="#" method="post"
      onsubmit="onSubmitFormAjax(this, Control.Modal.refresh);">
    {{mb_key   object=$geste_perop}}
    {{mb_class object=$geste_perop}}

  <input type="hidden" name="categorie_id" value="{{$evenement_categorie->_id}}" />
</form>

<form name="edit-evenement-{{$evenement_categorie->_guid}}" method="post" action="" enctype="multipart/form-data" onsubmit="return onSubmitFormAjax(this, Control.Modal.close);">
  <input type="hidden" name="m" value="dPsalleOp" />
  {{mb_class object=$evenement_categorie}}
  {{mb_key object=$evenement_categorie}}

  {{mb_field object=$evenement_categorie field=group_id hidden=true}}

  <table class="main form">
    {{mb_include module=system template=inc_form_table_header object=$evenement_categorie}}

      <tr>
        <th>{{mb_label object=$evenement_categorie field=libelle}}</th>
        <td>{{mb_field object=$evenement_categorie field=libelle}}</td>
      </tr>
      <tr>
        <th>{{mb_label object=$evenement_categorie field=description}}</th>
        <td>{{mb_field object=$evenement_categorie field=description}}</td>
      </tr>
      <tr>
        <th>{{mb_label object=$evenement_categorie field=chapitre_id}}</th>
        <td>
          {{if $chapitres && $chapitres|@count}}
            {{mb_field object=$evenement_categorie field=chapitre_id options=$chapitres}}
          {{else}}
            <span class="empty">{{tr}}CAnesthPeropChapitre.none{{/tr}}</span>
          {{/if}}
        </td>
      </tr>
      <tr>
        <th>{{mb_label object=$evenement_categorie field=actif}}</th>
        <td>{{mb_field object=$evenement_categorie field=actif}}</td>
      </tr>

      {{if $evenement_categorie->_id}}
        <tr>
          <th title="{{tr}}CAnesthPeropCategorie-Associate a Perop gesture with a category-desc{{/tr}}">{{tr}}CGestePerop{{/tr}}</th>
          <td>
            <input type="text" name="geste_perop_id_view" value="" />
          </td>
        </tr>
      {{/if}}

      <tr>
        <th>
          <span title="{{tr}}CAnesthPeropCategorie-picture-desc{{/tr}}">
            {{tr}}CAnesthPeropCategorie-picture{{/tr}}
          </span>
        </th>
        <td>
          {{mb_include module=system template=inc_inline_upload}}
        </td>
      </tr>

    {{mb_include module=system template=inc_form_table_footer object=$evenement_categorie options_ajax="Control.Modal.close"}}
  </table>
</form>

{{if $evenement_categorie->_id}}
  <br>
  <br>
  <table class="layout main">
    <tr>
      {{if $file->_id}}
        <td class="quarterPane">
          <table class="tbl">
            <tr>
              <th class="title">{{tr}}CAnesthPeropCategorie-Preview of the associated image{{/tr}}</th>
            </tr>
            <tr>
              <td class="categorie_preview">
                <div class="categorie_img">
                <span onmouseover="ObjectTooltip.createEx(this, '{{$file->_guid}}')">
                  {{thumbnail document=$file profile=medium style="max-width:150px;"}}
                </span>
                  <span style="margin-right: 5px;">
            <form name="editFile{{$file->_id}}" action="?" target="#" method="post"
                  onsubmit="return onSubmitFormAjax(this, Control.Modal.refresh);">
              {{mb_key   object=$file}}
                {{mb_class object=$file}}
              <input type="hidden" name="del" value="1" />
              <button type="button" onclick="this.form.onsubmit();" title="{{tr}}Delete{{/tr}}">
                <i class="fas fa-trash-alt" style="font-size: 1.2em;"></i>
              </button>
            </form>
          </span>
                </div>
              </td>
            </tr>
          </table>
        </td>
      {{/if}}
      <td>
          {{assign var=gestes_perop value=$evenement_categorie->_ref_gestes_perop}}
        <table class="tbl">
          <tr>
            <th class="title" colspan="3">{{tr}}CAnesthPeropCategorie-Associated Perop Gestures{{/tr}} ({{$gestes_perop|@count}})</th>
          </tr>
          <tr>
            <th>{{mb_title class=CGestePerop field=libelle}}</th>
            <th class="narrow">{{tr}}common-Action{{/tr}}</th>
          </tr>
          {{foreach from=$gestes_perop item=_geste_perop}}
            <tr>
              <td>
                <span onmouseover="ObjectTooltip.createEx(this, '{{$_geste_perop->_guid}}')">
                  {{$_geste_perop->_view}}
                </span>
              </td>
              <td class="button">
                <form name="editFile{{$_geste_perop->_id}}" action="?" target="#" method="post">
                    {{mb_key   object=$_geste_perop}}
                    {{mb_class object=$_geste_perop}}
                  <input type="hidden" name="categorie_id" value="" />
                  <button type="button" onclick="GestePerop.confirmDissociateElement(this.form, 'geste');"
                          title="{{tr}}CAnesthPeropCategorie-action-Dissociate the Perop gesture from the category{{/tr}}">
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
