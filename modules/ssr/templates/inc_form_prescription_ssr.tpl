{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  modalWindow = null;
  
  updateFieldsElementSSR = function(selected, oFormElement, category_id) {
    Element.cleanWhitespace(selected);
    var dn = selected.childNodes;
    
    if (dn[0].className != 'informal') {
      // On vide l'autocomplete
      $V(oFormElement.libelle, '');
      
      // On remplit la categorie et l'element_id dans le formulaire de creation de ligne
      var oForm = getForm("addLineSSR");
      $V(oForm._category_id, category_id);
      
      // Si la prescription existe, creation de la ligne
      if ($V(oForm.prescription_id)) {
        $V(oForm.element_prescription_id, dn[0].firstChild.nodeValue);
        return onSubmitFormAjax(oForm, { onComplete: updateListLines });
        //updateModal();
      }
      // Sinon, creation de la prescription
      else {
        $V(oForm.element_prescription_id, dn[0].firstChild.nodeValue, false);
        var oFormPrescriptionSSR = getForm("addPrescriptionSSR");
        return onSubmitFormAjax(oFormPrescriptionSSR); 
      }
    }
  };

  updateFormLine = function(prescription_id) {
    var oFormLineSSR = getForm("addLineSSR");
    $V(oFormLineSSR.prescription_id, prescription_id, $V(oFormLineSSR.element_prescription_id) ? true : false);

    if (document.forms.applyProtocole) {
      var oFormProt = getForm("applyProtocole");
      $V(oFormProt.prescription_id, prescription_id, $V(oFormProt.pack_protocole_id) ? true : false);
    }
  };

  updateListLines = function(category_id, prescription_id, full_line_id){
    var oFormLine = getForm("addLineSSR");
    
    _category_id = isNaN(category_id) ? $V(oFormLine._category_id) : category_id;
    _prescription_id = prescription_id ? prescription_id : $V(oFormLine.prescription_id);
    var url = new Url;
    url.setModuleAction("ssr", "ajax_vw_list_lines");
    url.addParam("category_id", _category_id);
    url.addParam("prescription_id", _prescription_id);
    url.addParam("full_line_id", full_line_id);
    url.requestUpdate("lines-"+_category_id);
  };

  viewModal = function() {
    Element.cleanWhitespace($('modal_SSR'));
    // Si la modale contient du texte, on l'affiche
    if($('modal_SSR').innerHTML != ''){
      modalWindow = Modal.open($('modal_SSR'), {
        className: 'modal'
      });
    } 
    // Sinon, on submit le formulaire de creation de ligne
    else {
      return onSubmitFormAjax(getForm('addLineSSR'), updateListLines);
    }
  };

  updateModal = function() {
    var oForm = getForm("addLineSSR");
    var url = new Url("ssr", "ajax_vw_modal");
    url.addParam("category_id", $V(oForm._category_id));
    url.addParam("element_prescription_id", $V(oForm.element_prescription_id));
    url.addParam("prescription_id", $V(oForm.prescription_id));
    url.requestUpdate("modal_SSR", { onComplete: viewModal } );
  };

  refreshFile = function(prot_id) {
    var url = new Url("ssr", "ajax_vw_list_files");
    url.addParam("object_id", prot_id.substr(5));
    url.addParam("object_class", "CPrescription");
    url.requestUpdate("files");
  };

  Main.add(function() {
    {{if $can_edit_prescription}}
      {{foreach from=$categories item=_category}}
        var url = new Url("prescription", "httpreq_do_element_autocomplete");
        url.addParam("category", "{{$_category->chapitre}}");
        url.addParam("category_id", "{{$_category->_id}}");
        url.autoComplete(getForm('search_{{$_category->_guid}}').libelle, "{{$_category->_guid}}_auto_complete", {
          dropdown: true,
          minChars: 2,
          updateElement: function(element) { updateFieldsElementSSR(element, getForm('search_{{$_category->_guid}}'), '{{$_category->_id}}') }
        } );
      {{/foreach}}
    {{/if}}

    var oFormProtocole = getForm("applyProtocole");
    if (oFormProtocole) {
      var url = new Url("prescription", "httpreq_vw_select_protocole");
      var autocompleter = url.autoComplete(oFormProtocole.libelle_protocole, "protocole_auto_complete", {
        dropdown: true,
        minChars: 2,
        valueElement: oFormProtocole.elements.pack_protocole_id,
        updateElement: function(selectedElement) {
          var node = $(selectedElement).down('.view');
          $V(oFormProtocole.libelle_protocole, node.innerHTML.replace("&lt;", "<").replace("&gt;",">"));
          if (autocompleter.options.afterUpdateElement)
            autocompleter.options.afterUpdateElement(autocompleter.element, selectedElement);
        },
        callback:
          function(input, queryString) {
            return (queryString + "&praticien_id={{$app->user_id}}");
          }
      } );
    }
  });

  submitProtocole = function() {
    return onSubmitFormAjax(getForm("applyProtocole"), refreshFormBilanSSR);
  };

  refreshFormBilanSSR = function() {
    var url = new Url("{{$m}}", "ajax_form_bilan_ssr");
    url.addParam("sejour_id", "{{$sejour->_id}}");
    url.requestUpdate("bilan");
  };

  refreshAfterDuplicate = function(line_id) {
    updateListLines(null, "{{$prescription->_id}}", line_id);
    $V(getForm("addLineSSR").callback, "");
  };

  duplicateSSRLine = function(element_prescription_id, category_id) {
    var form = getForm("addLineSSR");
    $V(form._category_id, category_id);
    $V(form.element_prescription_id, element_prescription_id);
    $V(form.callback, "refreshAfterDuplicate");
    return onSubmitFormAjax(form, updateListLines);
  };
</script>

{{mb_script module=prescription script=prescription}}

<div id="modal_SSR" style="display: none;"></div>

<!-- Formulaire de creation de lignes de prescription -->
<form name="addLineSSR" method="post" onsubmit="return checkForm(this);">
  <input type="hidden" name="m" value="prescription" />
  <input type="hidden" name="dosql" value="do_prescription_line_element_aed" />
  <input type="hidden" name="prescription_line_element_id" value=""/>
  {{if $app->_ref_user->isProfessionnelDeSante()}}
    <input type="hidden" name="signee" value="1" />
  {{/if}}
  <input type="hidden" name="del" value="0" />
  <input type="hidden" name="prescription_id" value="{{$prescription->_id}}"
         onchange="return onSubmitFormAjax(this.form, function() {
             if (getForm('searchElement')) {
               refreshFormBilanSSR();
            }
            else {
              updateListLines(); 
            } 
         } );"/>
  <input type="hidden" name="praticien_id" value="{{$app->user_id}}" />
  <input type="hidden" name="creator_id" value="{{$app->user_id}}" />
  <input type="hidden" name="element_prescription_id" value="" />
  <input type="hidden" name="debut" value="current" />
  <input type="hidden" name="callback" value="" />
  <input type="hidden" name="_category_id" value=""/>
</form>

<!-- Formulaire de modification de ligne -->
<form name="editLine" method="post">
  <input type="hidden" name="m" value="prescription" />
  <input type="hidden" name="dosql" value="do_prescription_line_element_aed" />
  <input type="hidden" name="del" value="0" />
  <input type="hidden" name="prescription_line_element_id" value=""/>
  <input type="hidden" name="date_arret" value=""/>
</form>

<!-- Formulaire d'ajout de prescription -->
<form name="addPrescriptionSSR" method="post" onsubmit="return checkForm(this);">
  <input type="hidden" name="m" value="prescription" />
  <input type="hidden" name="dosql" value="do_prescription_aed" />
  <input type="hidden" name="del" value="0" />
  <input type="hidden" name="prescription_id" value=""/>
  <input type="hidden" name="object_id" value="{{$sejour->_id}}" />
  <input type="hidden" name="object_class" value="CSejour" />
  <input type="hidden" name="type" value="sejour" />
  <input type="hidden" name="callback" value="updateFormLine" />
</form>

<table class="form">
  {{if $app->_ref_user->isPraticien() || $can->admin}}
    <!-- Praticien ou admin -->
    <tr>
      <td colspan="2">
        <form name="applyProtocole" method="post" onsubmit="if (!this.prescription_id.value) { return onSubmitFormAjax(getForm('addPrescriptionSSR'))} else { return submitProtocole(); };">

          <table>
            <tr>
              <td>
                <input type="hidden" name="m" value="prescription" />
                <input type="hidden" name="dosql" value="do_apply_protocole_aed" />
                <input type="hidden" name="del" value="0" />
                <input type="hidden" name="prescription_id" value="{{$prescription->_id}}" onchange="this.form.onsubmit();"/>
                <input type="hidden" name="praticien_id" value="{{$app->user_id}}" />
                <input type="hidden" name="pratSel_id" value="" />
                <input type="hidden" name="_active" value="1" />
                
               <input type="hidden" name="pack_protocole_id" value="" onchange="refreshFile(this.value)"/>
              </td>
            </tr>
            <tr>
              <td>
                <input type="text" name="libelle_protocole" value="&mdash; Choisir un protocole" class="autocomplete" style="font-weight: bold; font-size: 1.3em; width: 200px;"/>
                <div style="display:none; width: 350px;" class="autocomplete" id="protocole_auto_complete"></div>
              </td>
              <td>
                <div id="files" style="float:left"> {{mb_include module=ssr template=inc_vw_list_files count_object=0}}</div>
              </td>
              <td>
                <button type="submit" class="submit">{{tr}}Apply{{/tr}}</button>
              </td>
            </tr>
          </table>
        </form>
      </td>
    </tr>
  {{else}}
  <!-- Ni praticien ni admin -->
  <tr>
    <td colspan="2">
      <script>
        Main.add(function() {
          var url = new Url("ssr", "ajax_autocomplete_prescription_executant");
          url.autoComplete(getForm("searchElement").libelle, "searchElement_auto_complete", {
            minChars: 2,
            dropdown: true,
            updateElement: function(selected) {
              Element.cleanWhitespace(selected);
              var dn = selected.childNodes;
              var element_id = dn[0].firstChild.nodeValue;
            
              var oForm = getForm("addLineSSR");
              $V(oForm.element_prescription_id, element_id);
              
              // Si la prescription n'est pas encore créée, on la crée
              if (!$V(oForm.prescription_id)) {
                var oFormAddPrescription = getForm('addPrescriptionSSR');
                onSubmitFormAjax(oFormAddPrescription);
              }
              else {
                onSubmitFormAjax(oForm, refreshFormBilanSSR);
              }
            }
          } );
        } );
      </script>  

      <form name="searchElement">  
        <input type="text" name="libelle" value="" class="autocomplete"  style="font-weight: bold; font-size: 1.3em; width: 300px;"/>
        <input type="hidden" name="element_id" onchange="" />
        <div style="display:none;" class="autocomplete" id="searchElement_auto_complete"></div>
      </form>
    </td>
  </tr>  
  {{/if}}

  <tr>
    <th class="title" colspan="2">{{tr}}CPrescription{{/tr}}</th>
  </tr>

  {{foreach from=$categories item=_category}}
    {{assign var=category_id value=$_category->_id}}
    <tr>
      {{if $can_edit_prescription}}
        <th class="narrow">
          <strong onmouseover="ObjectTooltip.createEx(this, '{{$_category->_guid}}')">{{$_category}}</strong>
        </th>
        <td>
          <form name="search_{{$_category->_guid}}" action="?" method="post">
            <input type="text" name="libelle" value="" class="autocomplete" />
            <div style="display:none;" class="autocomplete" id="{{$_category->_guid}}_auto_complete"></div>
          </form>
        </td>
      {{else}}
        <td></td>
        <th style="text-align: left;">
          <strong>
          <strong onmouseover="ObjectTooltip.createEx(this, '{{$_category->_guid}}')">{{$_category}}</strong>
          </strong>
        </th>
      {{/if}}
    </tr>
    <tbody  id="lines-{{$category_id}}">
      {{assign var=full_line_id value=""}}
      {{mb_include module=ssr template=inc_list_lines nodebug=true}}
    </tbody>
  {{foreachelse}}
  <tr>
    <td colspan="2">
      <div class="small-info">{{tr}}CPrescription.none{{/tr}}</div>
    </td>
  </tr>
  {{/foreach}}
</table>
