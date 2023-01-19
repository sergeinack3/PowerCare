{{*
 * @package Mediboard\ihe
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=eai script=interop_actor}}

<button type="button" class="change" onclick="InteropActor.addProfilSupportedMessage();">
    {{tr}}CInteropActor-msg-Add profil supported message{{/tr}}
</button>

<br/>

<form name="migrationConfigs" method="get" onsubmit="return InteropActor.migrationConfigs(this)">
    <select name="actor">
        {{assign var=actors value='Ox\Interop\Eai\CInteropActor'|static:"actors_configs"}}
        {{foreach from=$actors item=_actor}}
            <option value="{{$_actor}}">
                {{tr}}{{$_actor}}{{/tr}}
            </option>
        {{/foreach}}
    </select>

    <button type="submit" class="change singleclick" id="button_submit_migration_configs">
        {{tr}}CInteropActor-msg-Migrate actor{{/tr}}
    </button>
</form>


<div id="add_profil_supported_messages"></div>
