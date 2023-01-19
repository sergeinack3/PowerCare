/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

togglePageLayout = function() {
  $("page_layout").toggle();
};

completeLayout = function() {
  var tab_margin = ["top", "right", "bottom", "left"];
  var form = getForm("editFrm");
  var dform = getForm('download-pdf-form');
  for(var i=0; i < 4; i++) {
    if ($("input_margin_"+tab_margin[i])) {
      $("input_margin_"+tab_margin[i]).remove();
    }
    dform.insert({bottom: new Element("input",{id: "input_margin_"+tab_margin[i],type: 'hidden', name: 'margins[]', value: $("editFrm_margin_"+tab_margin[i]).value})});
  }
  $V(dform.orientation, $V(form._orientation));
  $V(dform.page_format, form._page_format.value);
};

save_page_layout = function() {
  page_layout_save = {
    margin_top:    PageFormat.form.margin_top.value,
    margin_left:   PageFormat.form.margin_left.value,
    margin_right:  PageFormat.form.margin_right.value,
    margin_bottom: PageFormat.form.margin_bottom.value,
    page_format:   PageFormat.form._page_format.value,
    page_width:    PageFormat.form.page_width.value,
    page_height:   PageFormat.form.page_height.value,
    orientation:   $V(PageFormat.form._orientation),
    factory:       $V(PageFormat.form.factory)
  };
};

cancel_page_layout = function() {
  $V(PageFormat.form.margin_top,    page_layout_save.margin_top);
  $V(PageFormat.form.margin_left,   page_layout_save.margin_left);
  $V(PageFormat.form.margin_right,  page_layout_save.margin_right);
  $V(PageFormat.form.margin_bottom, page_layout_save.margin_bottom);
  $V(PageFormat.form._page_format,  page_layout_save.page_format);
  $V(PageFormat.form.page_height,   page_layout_save.page_height);
  $V(PageFormat.form.page_width,    page_layout_save.page_width);
  $V(PageFormat.form._orientation,  page_layout_save.orientation);
  $V(PageFormat.form.factory,       page_layout_save.factory);

  if(Thumb.thumb_up2date && !Thumb.changed) {
    Thumb.thumb_up2date = true;
    $('mess').hide();
    $('thumbs').setOpacity(1);
  }
  Control.Modal.close();
};

emptyPDF = function() {
  Thumb.old();

  // La requête de vidage de pdf doit être faite dans le scope
  // de la fenêtre principale, car on est en train de fermer la popup
  var f = getForm("download-pdf-form");

  if (Prototype.Browser.IE || !window.opener) {
    var url = new Url();
  }
  else {
    var url = new window.opener.Url();
  }
  url.addParam("m", "compteRendu");
  url.addParam("dosql", "do_modele_aed");
  url.addParam("_do_empty_pdf", 1);
  url.addParam("compte_rendu_id", $V(f.compte_rendu_id));

  url.requestJSON(function(){}, {method: "post"});
};