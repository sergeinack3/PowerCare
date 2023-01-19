{{*
 * @package Mediboard\Hprimxml
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
  <tr>
    <th class="category">Evénement</th>
    <th class="category">Version par défaut</th>
    <th class="category">Validation</th>
    <th class="category">Acquittement</th>
  </tr>

  {{assign var=evenements value='Ox\Interop\Hprimxml\CHPrimXML'|static:versions}}

  {{foreach from=$evenements key=_family item=_evts}}
    {{foreach from=$_evts key=_evt item=_versions}}
    <tr>
      <td>{{tr}}config-hprimxml-{{$_evt}}{{/tr}}</td>
      <td>
        <form name="editConfig_extract" method="post" onsubmit="return onSubmitFormAjax(this);">
          {{mb_configure module=$m}}

          <select name="hprimxml[{{$_evt}}][version]" onchange="this.form.onsubmit();">
            {{foreach from=$_versions item=_version}}
            <option value="{{$_version}}" {{if ($_version == $conf.hprimxml.$_evt.version)}}selected{{/if}}>v. {{$_version}}</option>
            {{/foreach}}
          </select>
        </form>
      </td>

      <td>
        <form name="editConfig_validation" method="post" onsubmit="return onSubmitFormAjax(this);">
          {{mb_configure module=$m}}

          <label for="hprimxml[{{$_evt}}][validation]_1">{{tr}}bool.1{{/tr}}</label>
          <input type="radio" name="hprimxml[{{$_evt}}][validation]" value="1"
                 onchange="this.form.onsubmit();" {{if $conf.hprimxml.$_evt.validation == "1"}}checked{{/if}} />
          <label for="hprimxml[{{$_evt}}][validation]_0">{{tr}}bool.0{{/tr}}</label>
          <input type="radio" name="hprimxml[{{$_evt}}][validation]" value="0"
                 onchange="this.form.onsubmit();" {{if $conf.hprimxml.$_evt.validation == "0"}}checked{{/if}} />
        </form>
      </td>
      <td>
        <form name="editConfig_send_ack" method="post" onsubmit="return onSubmitFormAjax(this);">
          {{mb_configure module=$m}}

          <label for="hprimxml[{{$_evt}}][send_ack]_1">{{tr}}bool.1{{/tr}}</label>
          <input type="radio" name="hprimxml[{{$_evt}}][send_ack]" value="1"
                 onchange="this.form.onsubmit();" {{if $conf.hprimxml.$_evt.send_ack == "1"}}checked{{/if}} />
          <label for="hprimxml[{{$_evt}}][send_ack]_0">{{tr}}bool.0{{/tr}}</label>
          <input type="radio" name="hprimxml[{{$_evt}}][send_ack]" value="0"
                 onchange="this.form.onsubmit();" {{if $conf.hprimxml.$_evt.send_ack == "0"}}checked{{/if}} />
        </form>
      </td>
    </tr>
    {{/foreach}}
  {{/foreach}}
</table>