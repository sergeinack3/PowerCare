{{*
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div class="only-printable">
    <h2>Documents manquants - P�riode du {{$date_min_relance|date_format:$conf.date}}
        au {{$date_max_relance|date_format:$conf.date}}</h2>

    <h3>
        {{if $status}}
            Statut : {{if $status == "datetime_creation"}}Premi�re relance{{elseif $status == "datetime_relance"}}Deuxi�me relance{{else}}Cl�tur�es{{/if}} &mdash;
        {{/if}}

        {{if $urgence}}
            Urgence : {{if $urgence == "normal"}}Normal{{else}}Urgent{{/if}} &mdash;
        {{/if}}

        {{if $type_doc}}
            Type doc : {{if $type_doc == "cra"}}CRA{{elseif $type_doc == "cro"}}CRO{{else}}Lettre de sortie{{/if}} &mdash;
        {{/if}}

        {{if $commentaire_med != ""}}
            Commentaire med. : {{if $commentaire_med == "0"}}Vide{{else}}Renseign�{{/if}} &mdash;
        {{/if}}

        {{if $chir->_id}}
            M�decin resp. : {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$chir}} &mdash;
        {{/if}}
    </h3>
</div>
