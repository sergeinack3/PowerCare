{{*
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=pdf_and_thumbs value=$app->user_prefs.pdf_and_thumbs}}

{{assign var=time_autosave value=$app->user_prefs.time_autosave}}

{{mb_script module=compteRendu script=modele}}
{{mb_script module=compteRendu script=thumb}}
{{mb_script module=compteRendu script=read_write_timer}}
{{if $pdf_and_thumbs}}
  {{mb_script module=compteRendu script=layout}}
{{/if}}

{{if $compte_rendu->_is_dompdf}}
  <div class="small-warning">
    {{tr}}CCompteRendu-Alert on dompdf{{/tr}}
  </div>
{{/if}}

<script>
  window.same_print = {{"dPcompteRendu CCompteRenduPrint same_print"|gconf}};

  // Taleau des categories en fonction de la classe du compte rendu
  var listObjectClass = {{$listObjectClass|@json}};
  var aTraducClass = {{$listObjectAffichage|@json}};

  loadObjectClass = function(value) {
    var form = document.editFrm;
    var select = $(form.elements.object_class);
    var children = select.childElements();

    if (children.length > 0)
      children[0].nextSiblings().invoke('remove');

    // Insert new ones
    $H(listObjectClass).each(function(pair){
      select.insert(new Element('option', {value: pair.key, selected: pair.key == value}).update(aTraducClass[pair.key]));
    });

    // Check null position
    select.fire("ui:change");

    loadCategory();
  };

  loadCategory = function(value) {
    var form = getForm("editFrm");
    var select = $(form.file_category_id);
    var children = select.childElements();

    if (children.length > 0) {
      children[0].nextSiblings().invoke("remove");
    }

    select.fire('ui:change');

    // Insert new ones
    var cats = listObjectClass[$V(form.object_class)];

    if (!cats) {
      return;
    }

    var keys = Object.keys(cats);

    keys.each(function(key) {
      select.insert(DOM.option({value: key, selected: key == value}, cats[key]));
    });
  };

  submitCompteRendu = function(callback) {
    // Do not store the content editable of the class field spans.
    {{if $compte_rendu->_id}}
      window.toggleContentEditable(true);
    {{/if}}

    (function(){
      let form = getForm("editFrm");
      if (checkForm(form) && User.id) {
        if (callback) {
          callback();
        }

        ReadWriteTimer.storeSave($V(form.compte_rendu_id), () => { form.submit(); });
      }
    }).defer();
  };

  setTemplateName = function(object_class, name, type) {
    var form = getForm("editFrm");
    var special_modele_area = $("special_modele");
    var button_cancel = form.down("button.cancel");

    if (name && object_class && type) {
      $V(form.object_class, object_class);
      $V(form.nom, name);
      $V(form.type, type);

      special_modele_area.innerHTML = $T("CCompteRendu.description_" + name);
      special_modele_area.show();

      button_cancel.show();
      form.nom.hide();
    }
    else {
      special_modele_area.hide();
      $V(form.nom, "");

      form.nom.show();
      button_cancel.hide();
    }

    Control.Modal.close();
  };
</script>

<script>
  Main.add(function () {
    loadObjectClass('{{$compte_rendu->object_class}}');
    loadCategory('{{$compte_rendu->file_category_id}}');

    Control.Tabs.create('tabs-edit');

    var form = getForm("editFrm");

    {{if $compte_rendu->_id}}
      Thumb.instance = CKEDITOR.instances.htmlarea;
      {{if $droit && $pdf_and_thumbs}}
        Thumb.modele_id = '{{$compte_rendu->_id}}';
        Thumb.user_id = '{{$user_id}}';
        Thumb.mode = "modele";
        PageFormat.init(getForm("editFrm"));
      {{/if}}
    {{/if}}

    {{if $compte_rendu->_id && $time_autosave}}
    // Sauvegarde automatique
    submitCompteRendu.delay({{$time_autosave}});
    {{/if}}

    form.factory.down('option[value="CDomPDFConverter"]').disabled = true;

    {{if $compte_rendu->file_category_id}}
      form.file_category_id.fire('ui:change');
    {{/if}}

    ReadWriteTimer.init('{{$compte_rendu->_id}}');
  });
</script>

<div id="choose_template_name" style="display: none; width: 600px;">
  <table class="tbl">
    {{foreach from='Ox\Mediboard\CompteRendu\CCompteRendu::getSpecialNames'|static_call:null item=_names_by_class key=_class}}
      <tr>
        <th class="category" colspan="3">{{tr}}{{$_class}}{{/tr}}</th>
      </tr>
      {{foreach from=$_names_by_class key=_name item=_type}}
        <tr>
          <td class="text">
            {{tr}}CCompteRendu.description_{{$_name}}{{/tr}}
          </td>
          <td class="narrow">
            <button type="button" class="tick notext"
              onclick="setTemplateName('{{$_class}}', '{{$_name}}', '{{$_type}}');"></button>
          </td>
        </tr>
      {{/foreach}}
    {{/foreach}}
    <tr>
      <td class="button" colspan="2">
        <button class="cancel" onclick="Control.Modal.close();">{{tr}}Close{{/tr}}</button>
      </td>
    </tr>
  </table>
</div>

{{if $pdf_and_thumbs}}
  <form style="display: none;" name="download-pdf-form" target="download_pdf" method="post"
    action="?m=compteRendu&a=ajax_pdf"
    onsubmit="PageFormat.completeForm();">
    <input type="hidden" name="content" value="" />
    <input type="hidden" name="compte_rendu_id" value="{{$compte_rendu->_id}}"/>
    <input type="hidden" name="suppressHeaders" value="1" />
    <input type="hidden" name="save_file" value="0" />
    <input type="hidden" name="header_id" value="" />
    <input type="hidden" name="footer_id" value="" />
    <input type="hidden" name="mode" value="" />
    <input type="hidden" name="type" value="" />
    <input type="hidden" name="height" value="0" />
    <input type="hidden" name="stream" value="1" />
    <input type="hidden" name="first_time" value="1" />
  </form>
{{/if}}

<iframe name="download_pdf" style="width: 500px; height: 500px; position: absolute; top: -1000px;"></iframe>

<form name="editFrm" action="?m={{$m}}" method="post" 
 onsubmit="Url.ping(submitCompteRendu); return false;"
 class="{{$compte_rendu->_spec}}">

  <input type="hidden" name="m" value="compteRendu" />
  <input type="hidden" name="del" value="0" />
  <input type="hidden" name="dosql" value="do_modele_aed" />
  {{mb_key object=$compte_rendu}}
  {{mb_field object=$compte_rendu field=object_id hidden=1}}
  {{mb_field object=$compte_rendu field=duree_ecriture hidden=1}}

  {{if !$droit}}
    <input type="hidden" name="group_id" />
    <input type="hidden" name="function_id" />
    <input type="hidden" name="user_id" value="{{$mediuser->_id}}" />
  {{/if}}
  {{if $compte_rendu->type != "body"}}
    <input type="hidden" name="fast_edit" value="{{$compte_rendu->fast_edit}}" />

    {{if !$pdf_and_thumbs}}
      <input type="hidden" name="fast_edit_pdf" value="{{$compte_rendu->fast_edit_pdf}}" />
    {{/if}}

  {{/if}}

  <table class="main">
    <tr>
      <td style="width: 300px;">
        <table class="form me-no-box-shadow">
          <tr>
            <th class="category me-entete" colspan="2">
              {{if $compte_rendu->_id}}
                {{mb_include module=system template=inc_object_notes      object=$compte_rendu}}
                {{mb_include module=system template=inc_object_idsante400 object=$compte_rendu}}
                {{mb_include module=system template=inc_object_history    object=$compte_rendu}}
              {{/if}}
              {{tr}}CCompteRendu-informations{{/tr}}
            </th>
          </tr>
        </table>

        <ul id="tabs-edit" class="control_tabs small">
          <li><a href="#info">{{tr}}CCompteRendu-part-informations{{/tr}}</a></li>
          <li><a href="#layout" id="a_addedit_modeles_mise_en_page">{{tr}}CCompteRendu-part-layout{{/tr}}</a></li>
        </ul>

        {{mb_include module=compteRendu template=inc_modele_info}}

        {{mb_include module=compteRendu template=inc_modele_layout with_factory=0}}

        <hr class="me-no-display" />

        <table class="form me-no-box-shadow">
          <tr>
            {{if $droit}}
              <td class="button" colspan="2">
              {{if $compte_rendu->_id}}
              <button id="button_addedit_modeles_save_mise_en_page" class="modify" type="submit">{{tr}}Save{{/tr}}</button>
              <button class="trash" type="button" onclick="confirmDeletion(this.form,{typeName:'le modèle',objName:'{{$compte_rendu->nom|smarty:nodefaults|JSAttribute}}'})">
              {{tr}}Delete{{/tr}}
              </button>
              {{else}}
              <button id="button_addedit_modeles_create" class="submit">{{tr}}Create{{/tr}}</button>
              {{/if}}
              </td>
            {{/if}}
          </tr>

          {{if $compte_rendu->_id}}
            <tr>
              <th class="category" colspan="2">{{tr}}CCompteRendu-other-actions{{/tr}}</th>
            </tr>

            <tr>
              <td class="button" colspan="2">
                 <button type="button" class="duplicate me-tertiary"
                         onclick="Modele.copy(this.form, '{{$user_id}}', '{{$droit}}')">{{tr}}Duplicate{{/tr}}</button>
                 <button id="button_addedit_modeles_preview" type="button" class="search me-tertiary"
                         onclick="Modele.preview('{{$compte_rendu->_id}}')">{{tr}}Preview{{/tr}}</button>
                 <button type="button" class="search me-tertiary"
                         onclick="Modele.showUtilisation('{{$compte_rendu->_id}}')">{{tr var1=$compte_rendu->_count_utilisation}}CCompteRendu-Use %s{{/tr}}</button>
              </td>
            </tr>
          {{/if}}
        </table>
      </td>
    
      <td style="height: 500px; max-width: 600px !important;" class="greedyPane">
        {{if $compte_rendu->_id}}
          {{if !$droit}}
            <div class="big-info">
              {{tr var1=$compte_rendu->_source|count_words}}CCompteRendu-msg-The template is readonly. It includes %s words. You can copy it for your own use by clicking on Duplicate{{/tr}}
            </div>
            <hr/>
          {{/if}}
          {{mb_field object=$compte_rendu field="_source" id="htmlarea" name="_source"}}
        {{/if}}
      </td>
      {{if $compte_rendu->_id && $pdf_and_thumbs}}
        <td id="thumbs_button" class="narrow">
          <div id="mess" class="oldThumbs opacity-60" style="display: none;">
          </div>
          <div id="thumbs" style="overflow: auto; overflow-x: hidden; width: 300px; text-align: center; white-space: normal;">
          </div>
        </td>
      {{/if}}
    </tr>
  </table>
</form>
