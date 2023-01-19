{{*
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=with_factory value=1}}

<script>

{{assign var=_page_formats value='Ox\Mediboard\CompteRendu\CCompteRendu'|static:"_page_formats"}}

var PageFormat = {
  form: null,
  formats: {{$_page_formats|@json}},
  
  init: function(form) {
    this.form = form;
    this.updateFormat();
  },
  
  updateOrientation: function() {
    if(parseInt(this.form.page_width.value) < parseInt(this.form.page_height.value)) {
      $V(this.form._orientation[0], true);
    }
    else {
      $V(this.form._orientation[1], true);
    }
  },
  
  updatePageDims: function() {
    var size = this.formats[$V(this.form._page_format)] || [21, 29.7];
    $V(this.form.page_width, size[0], false);
    $V(this.form.page_height, size[1], false);
    $V(this.form._orientation[0], true);
  },

  updateFormat: function() {
    var page_width  = parseFloat(this.form.page_width.value.replace(',','.'));
    var page_height = parseFloat(this.form.page_height.value.replace(',','.'));
    for (_format in this.formats) {
      if((this.formats[_format][0] == page_width && this.formats[_format][1] == page_height) ||
         (this.formats[_format][0] == page_height && this.formats[_format][1] == page_width)) {
        this.form._page_format.value = _format;
        break;
      }
      else {
        this.form._page_format.value = "";
      }
    }
    this.updateOrientation();
    this.changePage();
  },

  toggleMode: function(mode) {
    if (((mode == "landscape") && (parseFloat(this.form.page_width.value) < parseFloat(this.form.page_height.value))) ||
        ((mode == "portrait") && (parseFloat(this.form.page_width.value) > parseFloat(this.form.page_height.value)))) {
      var page_width  = this.form.page_width;
      var page_height = this.form.page_height;
      var a = page_width.value;
      page_width.value  = page_height.value;
      page_height.value = a;

      var oldtop = this.form.margin_top.value;
      if(mode == "landscape") {
        this.form.margin_top.value = this.form.margin_left.value;
        this.form.margin_left.value = this.form.margin_bottom.value;
        this.form.margin_bottom.value = this.form.margin_right.value;
        this.form.margin_right.value = oldtop;
      }
      else {
        this.form.margin_top.value = this.form.margin_right.value;
        this.form.margin_right.value = this.form.margin_bottom.value;
        this.form.margin_bottom.value = this.form.margin_left.value;
        this.form.margin_left.value = oldtop;
      }
      this.changePage();
    }
  },

  changePage: function() {
    var page_preview = $("page_preview");
    var page_width  = parseInt(this.form.page_width.value);
    var page_height = parseInt(this.form.page_height.value);
    var width_redim = 0; var height_redim = 0;
    if(page_width > page_height) {
      width_redim = 100;
      height_redim = ((page_height / page_width) * width_redim);
    }
    else {
      height_redim = 100;
      width_redim = ((page_width / page_height) * height_redim);
    }
    page_preview.height = height_redim;
    page_preview.width  = width_redim;
    page_preview.style.height = height_redim + "px";
    page_preview.style.width  = width_redim  + "px";
    this.changeMarges("top");
    this.changeMarges("left");
  },

  changeMarges: function(elem) {
    var page_preview = $("page_preview");
    var correspondance = {top: "bottom", left:"right"};
    var resol_correspondance = {top: "height", left: "width"};
    var y = correspondance[elem];
    var y_resol = resol_correspondance[elem];
    var resolution = page_preview[y_resol] / this.form["page_" + y_resol].value;
    page_preview.style["padding"+elem.capitalize()] = (resolution * this.form["margin_"+elem].value) + "px";
    page_preview.style["padding"+y.capitalize()] = (resolution * this.form["margin_"+y].value) + "px";
    page_preview.style[y_resol] = (page_preview[y_resol] - (resolution * this.form["margin_" + elem].value) -
                                  (resolution * this.form["margin_" + y].value)) + "px";
  },

  completeForm: function() {
    var tab_margin = new Array("top", "right", "bottom", "left");
    var dform = getForm("download-pdf-form");
    if ($("input_margin_top") != null) {
      for (var i = 0; i < 4; i++) {
        $("input_margin_"+tab_margin[i]).remove();
      }
    }
    $V(dform.header_id, this.form.header_id.value||0);
    $V(dform.footer_id, this.form.footer_id.value||0);
    $V(dform.mode, "modele");
    $V(dform.type, $V(PageFormat.form.type));
    $V(dform.height, this.form.height.value);
    {{if $compte_rendu->type == "body"}}
      var page_format = this.form._page_format.value;
      for (i=0; i < 4; i++) {
        dform.insert({bottom: new Element("input",{id: "input_margin_"+tab_margin[i],type: 'hidden', name: 'margins[]', value: $("editFrm_margin_"+tab_margin[i]).value})});
      }
      $V(dform.page_format, page_format);
      $V(dform.orientation, $V(this.form._orientation));
    {{/if}}
    dform.submit();
  }
};
</script>

<table class="layout me-no-box-shadow" style="margin: auto !important;">
  <tr>
    <td style="text-align: center" colspan="3">
      {{if $droit}}
        {{mb_field object=$compte_rendu field="margin_top" onchange="Thumb.old(); PageFormat.changeMarges('top');" onkeyup="PageFormat.changeMarges('top');" size="3" increment=true form=editFrm step=0.1}}cm
      {{else}}
        {{mb_field object=$compte_rendu field="margin_top" readonly="readonly"}}
      {{/if}}
    </td>
  </tr>

  <tr>
    <td style="vertical-align: middle;">
      {{if $droit}}
        {{mb_field object=$compte_rendu field="margin_left" onchange="Thumb.old(); PageFormat.changeMarges('left');" onkeyup="PageFormat.changeMarges('left');" size="3" increment=true form=editFrm step=0.1}}cm
      {{else}}
        {{mb_field object=$compte_rendu field="margin_left" readonly="readonly"}}
      {{/if}}
    </td>

    <!-- Aperçu de la page avec les marges et le mode -->
    <td style="margin: 4px; text-align: center;">
      <div id="page_preview" style="background: #fff; border: 1px solid #000;">
        <div style="border: 1px dotted #666; width: 100%; height: 100%; margin: -1px; font-size: 2px; text-align: left; white-space: normal; overflow: hidden;">
          {{mb_include template=lorem_ipsum}}
        </div>
      </div>
    </td>

    <td style="text-align: right; vertical-align: middle;">
      {{if $droit}}
        {{mb_field object=$compte_rendu field="margin_right" onchange="Thumb.old(); PageFormat.changeMarges('left');" onkeyup="PageFormat.changeMarges('left');" size="3" increment=true form=editFrm step=0.1}}cm
      {{else}}
        {{mb_field object=$compte_rendu field="margin_right" readonly="readonly"}}
      {{/if}}
    </td>
  </tr>

  <tr>
    <td style="text-align: center" colspan="3">
      {{if $droit}}
        {{mb_field object=$compte_rendu field="margin_bottom" onchange="Thumb.old(); PageFormat.changeMarges('top');" onkeyup="PageFormat.changeMarges('top');" size="3" increment=true form=editFrm step=0.1}}cm
      {{else}}
        {{mb_field object=$compte_rendu field="margin_bottom" readonly="readonly"}}
      {{/if}}
    </td>
  </tr>
</table>

<table class="form me-no-box-shadow">
  <tr>
    {{me_form_field nb_cells=2 mb_object=$compte_rendu mb_field="_page_format" }}
      {{mb_field style="width: 12em" object=$compte_rendu field="_page_format" onchange="Thumb.old(); PageFormat.updatePageDims(); PageFormat.changePage();" emptyLabel="Autre"}}
    {{/me_form_field}}
  </tr>
  <tr>
    {{me_form_field nb_cells=2 mb_object=$compte_rendu mb_field="page_width"}}
      {{if $droit}}  
        {{mb_field object=$compte_rendu field="page_width" onchange="Thumb.old(); PageFormat.updateFormat();" onkeyup="PageFormat.updateFormat();" size="4" increment=true form=editFrm step=0.1}}cm
      {{else}}
        {{mb_field object=$compte_rendu field="page_width" readonly="readonly"}}
      {{/if}}
    {{/me_form_field}}
  </tr>
  <tr>
    {{me_form_field nb_cells=2 mb_object=$compte_rendu mb_field="page_height"}}
      {{if $droit}}
        {{mb_field object=$compte_rendu field="page_height" onchange="Thumb.old(); PageFormat.updateFormat();" onkeyup="PageFormat.updateFormat();" size="4" increment=true form=editFrm step=0.1}}cm
      {{else}}
        {{mb_field object=$compte_rendu field="page_height" readonly="readonly"}}
      {{/if}}
    {{/me_form_field}}
  </tr>
  <tr>
    <th>{{mb_label object=$compte_rendu field="_orientation"}}</th>
    <td>
      {{mb_field object=$compte_rendu typeEnum="radio"  field="_orientation" onclick="Thumb.old(); PageFormat.toggleMode(this.value);"}}
    </td>
  </tr>

  {{if $with_factory}}
  <tr>
    <th>{{mb_label object=$compte_rendu field="factory"}}</th>
    <td>
      {{mb_field object=$compte_rendu field="factory"}}
    </td>
  </tr>
  {{/if}}
</table>