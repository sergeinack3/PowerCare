{{*
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  var form;
  Main.add(function () {
    form = getForm("test");
    /*$('dom-creator').insert(
      DOM.div({className: 'small-info'},
        DOM.a({href: 'http://www.mozilla-europe.org', target: '_blank'},
          'Cette info est générée par le DOM creator !'
        )
      )
    );*/

    form.elements.sorted.sortByLabel();

    Calendar.regProgressiveField(form.progressive, {container: document.body});

    /*new AideSaisie.AutoComplete(form.elements.rques, {
      dependField1: form.elements.type,
      dependField2: form.elements.appareil,
      objectClass: "CAntecedent",
      userId: 25
    });*/

    var url = new Url("developpement", "ajax_autocomplete_test");
    url.autoComplete(form.autocomplete_1, $("autocomplete_list"), {dropdown: true, valueElement: $(form.autocomplete_value)});

    Control.Tabs.create("mb_field_test", true);
  });
</script>

{{if !$dialog}}
<a href="?m={{$m}}&a={{$tab}}&dialog=1">Lancer cette page sans les menus</a>
{{else}}
<a href="?m={{$m}}&tab={{$a}}">Lancer cette page avec les menus</a>
{{/if}}

<div id="dom-creator"></div>

<div id="modal" style="display: none;">test blah blah</div>

<button type="button" onclick="Modal.open($('modal'))">Modal window</button>
<button type="button" onclick="Modal.alert($('dom-creator'), {okLabel: 'Close', onValidate: function(){console.debug('Ok')} })">Alert !</button>
<button type="button" onclick="Modal.confirm($('dom-creator'), {onValidate: function(v){console.debug('Vous avez dit '+(v?'oui :)':'non :('))} })">Confirm ?</button>
<a href="http://www.google.fr" onclick="return window.open(this)">Popup</a>

<form name="test" action="?" method="get" onsubmit="if (checkForm(this)) {console.log('form.submit()');} return false;" id="form-test-id">
  <textarea name="rques"></textarea>
  
  <input type="hidden" name="m" value="{{$m}}" />
  <input type="hidden" name="tab" value="{{$tab}}" />
  
  <ul class="control_tabs" id="mb_field_test">
    <li><a href="#mb_field">mb_field</a></li>
    <li><a href="#mb_value">mb_value</a></li>
  </ul>

  <table class="form" id="mb_field" style="display: none;">
  {{foreach from=$specs item=class key=spec}}
    <tr>
      <th>{{mb_label object=$object field=$spec}}</th>
      <td>{{mb_field object=$object field=$spec form=test register=1 increment=1}}</td>
    </tr>
  {{/foreach}}
  </table>

  <table class="form" id="mb_value" style="display: none;">
  {{foreach from=$specs item=class key=spec}}
    <tr>
      <th>{{mb_title object=$object field=$spec}}</th>
      <td>{{mb_value object=$object field=$spec}}</td>
    </tr>
  {{/foreach}}
  </table>
  
  <table class="form">
    <tr>
      <th></th>
      <td>
        <select id="sorted">
          <option value="c">&mdash;</option>
          <option value="c">c</option>
          <option value="e">e</option>
          <option value="b">b</option>
          <option value="a">a</option>
          <option value="d">d</option>
          <option value="f">f</option>
        </select>
      </td>
    </tr>
    <tr>
      <th><label for="text_1">Champ masqué</label></th>
      <td><input type="text" name="text_1" class="mask|+(99)S99S99S99P99P notNull"/></td>
    </tr>
    <tr>
      <th></th>
      <td>
        <input type="hidden" name="progressive" value="2009-0-0" />
        <input type="text" name="autocomplete_value" value="" disabled="disabled" readonly="readonly" />
        <input type="text" name="autocomplete_1" />
        <div id="autocomplete_list" class="autocomplete"></div>
      </td>
    </tr>
    <tr>
      <th><label for="user_username">Login</label></th>
      <td><input type="text" name="user_username" value="fabien" class="str" /></td>
    </tr>
    <tr>
      <th><label for="text_2">Mot de passe</label></th>
      <td><input type="password" name="text_2" value="123456789" 
               class="password minLength|6 notContaining|user_username notNear|user_username alphaAndNum"
               onkeyup="checkFormElement(this)" />
               <div id="text_2_message"></div>
     </td>
   </tr>
   <tr>
     <td colspan="2" class="button">
        <button type="button" name="button_1" onclick="console.debug($(this.form.text_2).caret(3, 6, 'toto'))">Caret</button>
        <button type="submit" name="submit_1">OK</button>
      </td>
    </tr>
  </table>
</form>
