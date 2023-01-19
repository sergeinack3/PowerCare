{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=jfse script=Jfse}}

<script type="text/javascript">
    provisionDataForUser = (form) => {
        if ($V(form.elements['jfse_user_id']) === '') {
            Modal.alert('Veuillez sélectionner un utilisateur');
        } else {
            let parameters = {
                jfse_user_id : $V(form.elements['jfse_user_id'])
            };

            if ($V(form.elements['year']) !== '') {
                parameters.year = $V(form.elements['year']);
            }

            Jfse.displayView('provisioning/provisionData', 'results', parameters);
        }

        return false;
    }

    Main.add(() => {
        Jfse.setInputNotNull(getForm('provisioning').elements['jfse_user_id']);
    });
</script>

<form name="provisioning" method="get" action="?" onsubmit="return provisionDataForUser(this)">
    <table class="form">
        <tr>
            <th class="title" colspan="2">Provisioning de données</th>
        </tr>
        <tr>
            {{me_form_field nb_cells=1 label='CJfseUser'}}
                <select name="jfse_user_id">
                    <option value="">&mdash; {{tr}}Select{{/tr}}</option>
                    {{foreach from=$users item=user}}
                        <option value="{{$user->_id}}">
                            {{$user->_mediuser}}{{if $user->_mediuser->adeli}} &mdash; {{$user->_mediuser->adeli}}{{/if}}
                        </option>
                    {{/foreach}}
                </select>
            {{/me_form_field}}
            {{me_form_field nb_cells=1 label='Année'}}
                <input type="number" name="year" size="4"/>
            {{/me_form_field}}
        </tr>
        <tr>
            <td class="button" colspan="2">
                <button type="button" class="me-primary save" onclick="this.form.onsubmit();">Provisioner</button>
            </td>
        </tr>
    </table>
</form>

<div id="results">

</div>
