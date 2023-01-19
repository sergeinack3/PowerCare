{{*
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  changePage = function (page) {
    var url = new Url('dPdeveloppement', 'vw_integrate_translations');
    url.addParam('start', page);
    url.requestUpdate('tab-vw-integrate-translations');
  };

  checkAll = function (input) {
    $$('.translation-integrate').each(function (elem) {
      elem.checked = input.checked;
    });
  };

  integrateTranslations = function () {
    var translations = [];
    $$('.translation-integrate').each(function (elem) {
      if (elem.checked) {
        translations.push(elem.value);
      }
    });

    if (!translations.length) {
      alert('Il faut cocher au moins une traduction');
      return;
    }

    var form = getForm('integrate-translations');
    $V(form.elements.translations_ids, translations.join('|'));
    form.onsubmit();
  }
</script>

<form name="integrate-translations" method="post" onsubmit="return onSubmitFormAjax(this, {
        onComplete: function() {
        changePage({{$start}});
        }
        })">
  <input type="hidden" name="m" value="dPdeveloppement"/>
  <input type="hidden" name="dosql" value="do_integrate_translations"/>
  <input type="hidden" name="translations_ids" value=""/>

</form>

<table class="main tbl">
  <tr>
    <td colspan="5">
      {{mb_include module=system template=inc_pagination total=$total current=$start step=$step change_page="changePage"}}
    </td>
  </tr>

  <tr>
    <td colspan="5" align="right">
      <button type="button" class="save"
              onclick="integrateTranslations();">{{tr}}CTranslationOverwrite-integrate translations{{/tr}}</button>
    </td>
  </tr>

  <tr>
    <th>
      <input type="checkbox" onclick="checkAll(this);"/>
    </th>
    <th>{{mb_title class=CTranslationOverwrite field=source}}</th>
    <th>{{tr}}CTranslationOverwrite-_old_translation{{/tr}}</th>
    <th>{{mb_title class=CTranslationOverwrite field=translation}}</th>
    <th>{{mb_title class=CTranslationOverwrite field=language}}</th>
  </tr>

  {{foreach from=$translations key=_module item=_values}}
    <tr>
      <th class="section" colspan="5">{{tr}}module-{{$_module}}-court{{/tr}}</th>
    </tr>
    {{foreach from=$_values item=_trans}}
      <tr>
        <td class="narrow">
          <input type="checkbox" class="translation-integrate" value="{{$_trans.id}}"/>
        </td>
        <td><strong><samp>{{$_trans.key}}</samp></strong></td>
        <td class="text">{{$_trans.old_value}}</td>
        <td class="text">{{$_trans.value}}</td>
        <td class="narrow">{{$_trans.language}}</td>
      </tr>
    {{/foreach}}
    {{foreachelse}}
    <tr>
      <td class="empty" colspan="5">
        {{tr}}CTranslationOverwrite.none{{/tr}}
      </td>
    </tr>
  {{/foreach}}
</table>