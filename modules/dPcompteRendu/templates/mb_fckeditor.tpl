{{*
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=mode_play value=$app->user_prefs.mode_play}}

// Preloading extra plugins
window.list_plugins = [
  "apicrypt",
  "dropoff",
  "mbbenchmark",
  "mbcap",
  "mbcopy",
  "mbdate",
  "mbfields",
  "mbfooter",
  "mbfreetext",
  "mbheader",
  "mbhelpers",
  "mblineheight",
  "mblists",
  "mbpagebreak",
  "mbplay",
  "mbprint",
  "mbprinting",
  "mbprintPDF",
  "mbreplace",
  "mbspace",
  "mbthumbs",
  "mssante",
  "mssanteIHEXDM",
  "medimail",
  "usermessage"
];

date = new Date();
date = Math.round(date.getTime()/3600000);

list_plugins.each(function(plugin) {
  CKEDITOR.plugins.addExternal(plugin, "../../modules/dPcompteRendu/fcke_plugins/" + plugin + "/", "plugin.js?"+date);
});

CKEDITOR.editorConfig = function(config) {
  config.language = 'fr';
  config.defaultLanguage = 'fr';
  config.contentsLanguage = 'fr';
  config.enterMode = CKEDITOR.ENTER_BR;
  config.allowedContent = true;
  config.title = false;
  //config.startupFocus = true;
  config.pasteFromWordPromptCleanup = true;
  config.pasteFromWordRemoveFontStyles = '1';
  config.pasteFromWordRemoveStyles = '1';
  config.fontSize_sizes  = '8/8pt;9/9pt;10/10pt;11/11pt;12/12pt;14/14pt;16/16pt;18/18pt;20/20pt;22/22pt;24/24pt;26/26pt;28/28pt;36/36pt;48/48pt;72/72pt;';
  config.fontSize_sizes += 'xx-small/xx-small;x-small/x-small;small/small;medium/medium;large/large;x-large/x-large;xx-large/xx-large';

  config.font_names = "{{$conf.dPcompteRendu.CCompteRendu.default_fonts}}";

  var css = ["style/mediboard_ext/htmlarea.css?build={{app_version_key}}"];
  if (Prototype.Browser.IE) {
  css.push("style/mediboard_ext/ie.css?build={{app_version_key}}");
  }
  config.contentsCss = css;

  config.docType = '<!DOCTYPE html>';
  config.filebrowserImageBrowseUrl = "custom";
  config.tabSpaces = 13;
  config.indentOffset = 10;
  config.disableNativeSpellChecker = false;
  config.resize_maxWidth = "100%";
  config.resize_minWidth = "100%";
  config.entities_additional="#039";

  config.font_defaultLabel = '{{$conf.dPcompteRendu.CCompteRendu.default_font}}';
  {{if $templateManager->font != ""}}
    config.font_defaultLabel = '{{$templateManager->font}}';
  {{/if}}

  config.fontSize_defaultLabel = '{{"dPcompteRendu CCompteRendu default_size"|gconf}}'
  {{if $templateManager->size != ""}}
    config.fontSize_defaultLabel = '{{$templateManager->size}}'
  {{/if}}

  // Suppression du redimensionnement manuel
  config.resize_enabled = false;
  // Suppression du bouton de masquage des barres d'outils
  config.toolbarCanCollapse = false;
  // Suppression de la barre d'état avec la dom
  config.removePlugins = 'elementspath,iframe,magicline,showblocks,templates,wsc{{if $templateManager->printMode}},save{{/if}}';

  config.extraPlugins = window.list_plugins.join(',');

  {{if $templateManager->printMode}}
    config.toolbar = [['Preview' {{if $app->user_prefs.show_old_print}},'Print'{{/if}} {{if $app->user_prefs.pdf_and_thumbs}},'mbprintPDF'{{/if}}, '-','Find', 'usermessage'{{if $use_apicrypt}},'apicrypt' {{/if}}{{if $use_medimail}},'medimail', 'mssanteIHEXDM'{{/if}}{{if $use_mssante}},'mssante'{{/if}}]];
  {{elseif $templateManager->simplifyMode}}
    config.toolbar = [
    ['Save', 'Preview'],
    ['Font', 'FontSize'],
    ['Bold', 'Italic', 'Underline', '-', 'Subscript', 'Superscript'],
    ['JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock'],
    ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent'],
    ['TextColor', 'BGColor']
    ];
   {{elseif $templateManager->messageMode}}
      config.toolbar = [
        ['Font', 'FontSize'],
        ['Bold', 'Italic', 'Underline', '-', 'Subscript', 'Superscript'],
        ['JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock'],
        ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent'],
        ['TextColor', 'BGColor']
      ];
  {{else}}
    {{if $app->user_prefs.saveOnPrint || ($app->user_prefs.pdf_and_thumbs)}}
      var textForPrint = 'mbprint';
    {{else}}
      var textForPrint = 'Print';
    {{/if}}

    config.toolbar = [
      ['Save','Preview'], [{{if $app->user_prefs.pdf_and_thumbs}}'mbprintPDF',{{/if}}{{if $app->user_prefs.show_old_print}}textForPrint,{{/if}}'mbprinting', 'SelectAll', 'Cut', 'Copy', 'PasteText', 'PasteFromWord', 'Find', 'Undo', 'Redo'],
      [{{if !$templateManager->isModele}}'mbheader', 'mbfooter',{{/if}} 'mbpagebreak'],
      ['Table','HorizontalRule', 'Image', 'SpecialChar', 'mbspace', 'Checkbox'],
      ['Maximize', 'Source'], '/',
      ['Font', 'FontSize'],
      ['RemoveFormat', 'Bold', 'Italic', 'Underline', 'Strike', 'mbcap', 'mbreplace'],
      ['TransformTextSwitcher', 'TransformTextToLowercase', 'TransformTextToUppercase', 'TransformTextCapitalize'],
      ['Subscript', 'Superscript', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock', 'NumberedList', 'BulletedList'],
      ['Outdent', 'Indent', 'mblineheight', 'TextColor', 'BGColor'],'/',
      [{{if !$templateManager->isModele && $mode_play}}'mbplay', {{/if}} 'mbfields', {{if $templateManager->isModele}}'mblists', 'mbfreetext', {{/if}}{{if !$templateManager->isModele}}'mbhelpers', 'mbdate', 'usermessage', {{if $use_apicrypt}}'apicrypt', {{/if}}{{if $use_medimail}}'medimail', 'mssanteIHEXDM',{{/if}}{{if $use_mssante}}'mssante', {{/if}}'mbcopy', {{/if}}{{if $can->admin}}'mbthumbs', {{/if}}{{if $app->user_prefs.pdf_and_thumbs}}'mbhidethumbs',{{/if}} 'mbbenchmark']
    ];

    window.parent.fields = [];
    window.parent.listeChoix = [];
    window.parent.helpers = [];

    // Champs
    window.fields = {
      commandName: "MbField",
      spanClass: {{if $templateManager->valueMode}}"value"{{else}}"field"{{/if}},
      commandLabel: "Champs",
      options: {{$templateManager->sections|@json|smarty:nodefaults}},
      max_sections: {{$templateManager->max_sections}}
    };

    // Liste de choix
    {{if !$templateManager->valueMode}}
      window.parent.listeChoix.push({
      commandName: "MbNames",
      spanClass: "name",
      commandLabel: "Liste de choix",
      options: {{$templateManager->lists|@json|smarty:nodefaults}}
      });
    {{/if}}

    // Aides à la saisie
    window.parent.helpers.push({
      commandName: "MbHelpers",
      spanClass: "helper",
      commandLabel: "Aides &agrave; la saisie",
      options: {{$templateManager->helpers|@json|smarty:nodefaults}}
    });
  {{/if}}
}
