{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=errors value=0}}

<script type="text/javascript">
  editTrad = function (id) {
    var url = new Url("system", "ajax_edit_translation");
    url.addParam("trad_id", id);

    url.requestModal(700, 400);
  };

  changePage = function (start) {
    console.log(start);
    var form = getForm('search-translation');

    if (start !== undefined) {
      $V(form.elements.start, start);
    }

    form.onsubmit();
    // var url = new Url("system", "ajax_search_translation");
    // url.addParam('start', start);
    // url.requestUpdate("translation-overwrite-vw");
  };

  vwImportTranslations = function () {
    var url = new Url('system', 'vw_import_translations');
    url.requestModal('80%', '90%', {
      onClose: function () {
        changePage();
      }
    });
  };


  confirmPurgeTranslations = function () {
    Modal.confirm(
      $T('CTranslationOverwrite-purge useless?'),
      {
        onOK: function () {
          var start = getForm('search-translation').elements.start;

          var url = new Url('system', 'do_purge_translations_overwrites', 'dosql');
          url.addParam('start', start);
          url.requestUpdate('systemMsg', {
            method: 'post', onComplete: function () {
              changePage()
            }
          });
        }
      }
    );
  };

  searchTranslation = function () {
    var form = getForm('search-translation');
    $V(form.elements.start, 0);
    form.onsubmit();
  }

  Main.add(function () {
    var form = getForm('search-translation');
    form.onsubmit();
  });
</script>

<form name="search-translation" method="get" onsubmit="return onSubmitFormAjax(this, null, 'translation-overwrite-vw')">
  <input type="hidden" name="m" value="system"/>
  <input type="hidden" name="a" value="ajax_search_translation"/>
  <input type="hidden" name="start" value="0"/>

  <table class="main form">
    <tr>
      <td>
        <button onclick="editTrad(0)" type="button" class="new">{{tr}}CTranslationOverwrite.new{{/tr}}</button>

        {{if $can->admin}}
          <button type="button" id="remove-old-translations" class="trash" onclick="confirmPurgeTranslations();">
            {{tr}}system-action-purge translations{{/tr}}
          </button>
        {{/if}}
      </td>

      <td align="right" colspan="3">
        {{if $can->admin}}
          <button type="button" class="import" onclick="vwImportTranslations();">{{tr}}Import{{/tr}}</button>
        {{/if}}

        <a class="button download" href="?m=system&raw=ajax_export_translation" target="_blank">{{tr}}common-action-Export{{/tr}}</a>
      </td>
    </tr>

    <tr>
      <th>{{mb_title class=CTranslationOverwrite field=source}}</th>
      <td>{{mb_field class=CTranslationOverwrite field=source size=50 canNull=true}}</td>

      <th>{{mb_title class=CTranslationOverwrite field=language}}</th>
      <td>
        <select name="language">
          <option value="">{{tr}}All{{/tr}}</option>
          {{foreach from=$available_languages item=_lang}}
            <option value="{{$_lang}}">{{tr}}CTranslationOverwrite.language.{{$_lang}}{{/tr}}</option>
          {{/foreach}}
        </select>
      </td>
    </tr>

    <tr>
      <td colspan="4" class="button">
        <button type="button" class="search" onclick="searchTranslation()">{{tr}}Search{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>

<div id="translation-overwrite-vw"></div>