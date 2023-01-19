{{*
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=pdf_and_thumbs value=$app->user_prefs.pdf_and_thumbs}}
{{assign var=check_to_empty_field value=$app->user_prefs.check_to_empty_field}}
{{unique_id var=uid_fast_mode}}

<script>
  toggleOptions = function() {
    $$("#liste select").each(function(select) {
      select.size = select.size != 4 ? 4 : 1;
      select.multiple = !select.multiple;
      select.options[0].selected = false;
    } );
    $("multiple-info").toggle();
  };

  document.observe('keydown', function(e) {
    var keycode = Event.key(e);
    if (keycode == 27) {
      Control.Modal.close();
      $('fast-{{$unique_id}}').update();
    }
  });
  printFast = function() {
    var from = $("fast-edit-table-{{$uid_fast_mode}}").select(".freetext");
    var to = getForm("create-pdf-form-{{$uid_fast_mode}}");

    from.each(function(textarea) {
      $V(to[textarea.name], $V(textarea));
    });
  };

  preparePrintToServer = function(printer_id) {
    lockAllButtons();
    window.printer_id = printer_id;
    var oForm = getForm('create-pdf-form-{{$uid_fast_mode}}');
    $V(oForm.print_to_server, 1);
    getForm("fastModeForm-{{$uid_fast_mode}}").onsubmit();
  };

  linkFields = function(ref) {
    var tab = $('fast-edit-table-{{$uid_fast_mode}}');
    var form = getForm("fastModeForm-{{$uid_fast_mode}}");

    return [
      tab.select(".liste"),
      tab.select(".freetext"),
      tab.select(".empty_field"),
      tab.select(".destinataires"),
      [form.nom, form.file_category_id, form.__private]
    ];
  };

  lockAllButtons = function() {
    $$(".printer").each(function(item) {
      item.disabled = "disabled";
    });
  };

  generatePdf = function(id) {
    oState = $("state");
    oState.className = "loading";
    oState.setStyle({backgroundPosition: "50% 50%", height: '100px', textAlign: "center", marginTop: "1em", fontWeight: "bold"});
    oState.innerHTML = "{{tr}}CCompteRendu.generating_pdf{{/tr}}";
    var form = getForm('create-pdf-form-{{$uid_fast_mode}}');
    $V(form.compte_rendu_id, id);
    form.onsubmit();
  };

  printDoc = function(id) {
    Document.print(id);
    if (Document.refreshList) {
      Document.refreshList($V(getForm('fastModeForm-{{$uid_fast_mode}}').file_category_id), '{{$object_class}}', '{{$object_id}}');
      Control.Modal.close();
    }
    else {
      window.close();
    }
  };

  printToServer = function(file_id) {
    var url = new Url("compteRendu", "ajax_print");
    url.addParam("printer_id", window.printer_id);
    url.addParam("file_id", file_id);
    url.requestUpdate("systemMsg");
  };

  streamOrNotStream = function(form) {
    if ($V(form.stream) == 1) {
      $V(form.callback, "streamPDF");
      onSubmitFormAjax(form);
    }
    else {
      onSubmitFormAjax(form, {onComplete: function() {
        if (Document.refreshList) {
          Document.refreshList($V(getForm('fastModeForm-{{$uid_fast_mode}}').file_category_id), '{{$object_class}}', '{{$object_id}}');
          Control.Modal.close();
        }
        else {
          window.close();
        }
      }});
    }
  };

  streamPDF = function(id) {
    var form = getForm("stream-pdf-{{$uid_fast_mode}}");
    $V(form.file_id, id);
    form.submit();
    Document.refreshList($V(getForm('fastModeForm-{{$uid_fast_mode}}').file_category_id), '{{$object_class}}', '{{$object_id}}');
    Control.Modal.close();
  };

  switchToEditor = function() {
    window.saveFields = linkFields();
    Control.Modal.close();
    {{if $pack_id}}
      Document.createPack('{{$pack_id}}', '{{$object_id}}', null, null, 1);
    {{else}}
      Document.create('{{$modele_id}}','{{$object_id}}', '{{$object_id}}', '{{$object_class}}', 1);
    {{/if}}
  };

  toggleOptions = function() {
    $$("#liste select").each(function(select) {
      select.size = select.size != 4 ? 4 : 1;
      select.multiple = !select.multiple;
      select.options[0].selected = false;
    } );
    $("multiple-info").toggle();
  };

  Main.add(function() {
    {{if $lists|@count == 0 && $textes_libres|@count == 0 && $printers|@count <= 1}}
      {{if $printers|@count == 1}}
        $$(".printerServer")[0].click();
      {{else}}
        lockAllButtons();
        var oForm = getForm('fastModeForm-{{$uid_fast_mode}}');
        {{if $compte_rendu->fast_edit_pdf && $pdf_and_thumbs}}
          $V(getForm('create-pdf-form-{{$uid_fast_mode}}').stream, 1);
        {{else}}
          $V(oForm.callback, 'printDoc');
        {{/if}}
        oForm.onsubmit();
      {{/if}}
    {{/if}}
  });
</script>

<form name="stream-pdf-{{$uid_fast_mode}}" method="get" target="_blank">
  <input type="hidden" name="m" value="compteRendu" />
  <input type="hidden" name="raw" value="streamPDF" />
  <input type="hidden" name="file_id" value="" />
</form>

<form name="create-pdf-form-{{$uid_fast_mode}}" method="post" style="display: none;"
      onsubmit="printFast(); streamOrNotStream(this); return false;">
  <input type="hidden" name="compte_rendu_id" value='' />
  <input type="hidden" name="m" value="compteRendu" />
  <input type="hidden" name="dosql" value="do_pdf_cfile_aed" />
  <input type="hidden" name="stream" value="0" />
  <input type="hidden" name="print_to_server" value="0" />
  <input type="hidden" name="callback" value=""/>
</form>

<form name="fastModeForm-{{$uid_fast_mode}}" action="?m={{$m}}" method="post"
      onsubmit="if (User.id && checkForm(this)) { return onSubmitFormAjax(this, { useDollarV: true }); } return false;"
      class="{{$compte_rendu->_spec}}">
  <input type="hidden" name="m" value="compteRendu" />
  <input type="hidden" name="del" value="0" />
  <input type="hidden" name="dosql" value="do_modele_aed" />
  <input type="hidden" name="function_id" value="" />
  <input type="hidden" name="praticien_id" value="0" />
  <input type="hidden" name="group_id" value="" />
  <input type="hidden" name="modele_id" value="{{$modele_id}}" />
  <input type="hidden" name="compte_rendu_id" value="" />
  <input type="hidden" name="fast_edit" value="1" />
  <input type="hidden" name="fast_edit_pdf" value="{{$compte_rendu->fast_edit_pdf}}" />
  <input type="hidden" name="callback" value="generatePdf" />
  <input type="hidden" name="suppressHeaders" value="1"/>
  <input type="hidden" name="dialog" value="1"/>
  <input type="hidden" name="page_height" value="{{$compte_rendu->page_height}}" />
  <input type="hidden" name="page_width" value="{{$compte_rendu->page_width}}" />
  <input type="hidden" name="margin_left" value="{{$compte_rendu->margin_left}}"/>
  <input type="hidden" name="margin_right" value="{{$compte_rendu->margin_right}}"/>
  <input type="hidden" name="margin_top" value="{{$compte_rendu->margin_top}}"/>
  <input type="hidden" name="margin_bottom" value="{{$compte_rendu->margin_bottom}}"/>

  {{mb_field object=$compte_rendu field="author_id" hidden=1}}
  {{mb_field object=$compte_rendu field="object_id" hidden=1 prop=""}}
  {{mb_field object=$compte_rendu field="object_class" hidden=1 prop=""}}
  {{mb_field object=$compte_rendu field="font" hidden=1}}
  {{mb_field object=$compte_rendu field="size" hidden=1}}
  {{mb_field object=$compte_rendu field="factory" hidden=1}}
  {{mb_field object=$compte_rendu field="signataire_id" hidden=1}}
  {{mb_field object=$compte_rendu field="signature_mandatory" hidden=1}}
  {{mb_field object=$compte_rendu field="alert_creation" hidden=1}}
  {{mb_field object=$compte_rendu field="send" hidden=1}}
  {{mb_field object=$compte_rendu field="printer_id" hidden=1}}
  {{mb_field object=$compte_rendu field="_ext_cabinet_id" hidden=1}}

  <!-- the div is needed (textarea-container) -->
  <div style="display: none;">
    <textarea name="_source">{{$_source}}</textarea>
  </div>

  <table id="fast-edit-table-{{$uid_fast_mode}}" class="form" style="width: 100%; min-height: 200px;">
    <tr>
      <th class="title" colspan="2">
        {{tr}}CCompteRendu-fast_edit-title{{/tr}}
      </th>
    </tr>
    <tr>
      <th class="category" colspan="2">
        {{if $compte_rendu->_id}}
          {{mb_include module=system template=inc_object_idsante400 object=$compte_rendu}}
          {{mb_include module=system template=inc_object_history object=$compte_rendu}}
        {{/if}}
        {{mb_label object=$compte_rendu field=nom}}
        {{mb_field object=$compte_rendu field=nom}}

        &mdash;
        {{mb_label object=$compte_rendu field=file_category_id}}
        <select name="file_category_id">
          <option value="" {{if !$compte_rendu->file_category_id}}selected{{/if}}>&mdash; Aucune Catégorie</option>
          {{foreach from=$listCategory item=currCat}}
            <option value="{{$currCat->file_category_id}}"{{if $currCat->file_category_id==$compte_rendu->file_category_id}}selected{{/if}}>{{$currCat}}</option>
          {{/foreach}}
        </select>

        &mdash;

        <label>
          {{tr}}CCompteRendu-remis_patient{{/tr}}
          {{mb_field object=$compte_rendu field=remis_patient typeEnum="checkbox"}}
        </label>

        <label>
          {{tr}}CCompteRendu-private{{/tr}}
          {{mb_field object=$compte_rendu field=private typeEnum="checkbox"}}
        </label>
      </th>
    </tr>
    <tr>
      <td colspan="2">
        <button class="hslip" onclick="switchToEditor();" type="button">
          {{tr}}CCompteRendu.switchEditor{{/tr}}
        </button>
      </td>
    </tr>
    <tr>
      <td {{if $pdf_and_thumbs}}style="width: 80%;"{{else}}colspan="2"{{/if}}>
        <table style="width: 100%;">
          {{if $lists|@count}}
            <tr>
              <td id="liste" colspan="2">

                <!-- The div is required because of a Webkit float issue -->
                <div class="listeChoixCR">
                  <fieldset>
                    <legend>{{tr}}CListeChoix{{/tr}}</legend>
                  {{foreach from=$lists item=curr_list}}
                    <input type="checkbox" name="_empty_list[{{$curr_list->_id}}]"
                      title="{{if $check_to_empty_field}}{{tr}}CListeChoix.empty{{/tr}}{{else}}{{tr}}CListeChoix.fill{{/tr}}{{/if}}"
                      style="float: left;" class="empty_field"/>
                    <select name="_{{$curr_list->_class}}[{{$curr_list->_id}}][]" class="liste" {{if !$check_to_empty_field}}onchange="this.form.elements['_empty_list[{{$curr_list->_id}}]'].checked='checked'"{{/if}}>
                      <option value="undef">&mdash; {{$curr_list->nom}}</option>
                      {{foreach from=$curr_list->_valeurs item=curr_valeur}}
                        <option value="{{$curr_valeur}}" title="{{$curr_valeur}}">{{$curr_valeur|truncate}}</option>
                      {{/foreach}}
                    </select>
                  {{/foreach}}
                  </fieldset>
                </div>
              </td>
            </tr>
            <tr>
              <td class="button text" colspan="2">
                <div id="multiple-info" class="small-info" style="display: none;">
                {{tr}}CCompteRendu-use-multiple-choices{{/tr}}
                </div>
                <button class="hslip" type="button" onclick="toggleOptions();">{{tr}}Multiple options{{/tr}}</button>
              </td>
            </tr>
          {{/if}}

          {{if $textes_libres|@count}}
            {{foreach from=$textes_libres item=_nom}}
            <tr>
              <td colspan="2">
                <fieldset>
                  <legend>
                    {{$_nom|html_entity_decode}}
                    <input type="checkbox" name="_empty_texte_libre[{{$_nom|md5}}]"
                      title="{{if $check_to_empty_field}}{{tr}}CListeChoix.empty{{/tr}}{{else}}{{tr}}CListeChoix.fill{{/tr}}{{/if}}" class="empty_field"/>
                  </legend>
                  <textarea class="freetext" name="_texte_libre[{{$_nom|md5}}]"
                  {{if !$check_to_empty_field}}onkeydown="this.form.elements['_empty_texte_libre[{{$_nom|md5}}]'].checked='checked'; this.onkeydown=''"{{/if}}></textarea>
                  <input type="hidden" name="_texte_libre_md5[{{$_nom|md5}}]" value="{{$_nom}}"/>
                </fieldset>
                <script>
                  Main.add(function(){
                    new AideSaisie.AutoComplete(getForm("fastModeForm-{{$uid_fast_mode}}").elements["_texte_libre[{{$_nom|md5}}]"],
                    {
                      objectClass: '{{$compte_rendu->_class}}',
                      contextUserId: User.id,
                      contextUserView: "{{$user_view|smarty:nodefaults|JSAttribute}}",
                      timestamp: "{{$conf.dPcompteRendu.CCompteRendu.timestamp}}",
                      resetSearchField: false,
                      resetDependFields: false,
                      validateOnBlur: false,
                      property: "_source"
                    });
                  });
                </script>
              </td>
            </tr>
            {{/foreach}}
          {{/if}}

          <tr>
            <td>
              <div id="state" style="width: 100%; height: 100%"></div>
            </td>
            <td style="float: right;">
              <button class="tick oneclick printer" type="button" onclick="lockAllButtons(); this.form.onsubmit();">
                {{tr}}Save{{/tr}}
              </button>
              <fieldset style="display: inline; white-space: normal;" class="me-inline-block me-min-w110">
                <legend>{{tr}}Save{{/tr}} {{tr}}and{{/tr}}...</legend>
                <button class="printPDF printer oneclick" type="button"
                  onclick="lockAllButtons(); $V(getForm('create-pdf-form-{{$uid_fast_mode}}').stream, 1); this.form.onsubmit();">
                    {{tr}}Print{{/tr}}
                </button>
                {{if !$pdf_and_thumbs || !$compte_rendu->fast_edit_pdf}}
                  <button class="print printer oneclick" type="button"
                    onclick="lockAllButtons(); $V(getForm('fastModeForm-{{$uid_fast_mode}}').callback, 'printDoc'); this.form.onsubmit();">
                      {{tr}}Print{{/tr}}
                  </button>
                {{/if}}
                {{foreach from=$printers item=_printer}}
                  <button class="print printer oneclick printerServer" type="button"
                    onclick="preparePrintToServer('{{$_printer->_id}}');">
                    {{tr}}Print{{/tr}} ({{$_printer}})
                  </button>
                {{/foreach}}
              </fieldset>
            </td>
          </tr>
        </table>
      </td>
      {{if $pdf_and_thumbs}}
        <td style="height: 200px;">
          <div id="thumbs" style="overflow-x: hidden; width: 160px; text-align: center; white-space: normal; height: 200px;">
            {{if isset($file|smarty:nodefaults) && $file->_id}}
              {{thumbnail document_id=$modele_id document_class=CCompteRendu profile=medium style="width: 113px; height: 160px;"
                onclick="(new Url).ViewFilePopup('`$compte_rendu->object_class`', '`$compte_rendu->object_id`', '`$compte_rendu->_class`', '`$modele_id`');"}}
            {{else}}
              {{tr}}CCompteRendu.nothumbs{{/tr}}
            {{/if}}
          </div>
        </td>
      {{/if}}
    </tr>
  </table>
</form>
