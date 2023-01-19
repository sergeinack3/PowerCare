{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=config_mode value='Standard'}}
{{if $mode !== 'std'}}
    {{assign var=config_mode value='Alternatif'}}
{{/if}}

<div class="small-info">
  <ul>
    <li>Environnement actuel : <strong>{{tr}}config-instance_role-{{$conf.instance_role}}{{/tr}}</strong></li>
    <li>Vous param�trez actuellement le mode : <strong>{{$config_mode}}</strong></li>
  </ul>

  <button type="button" class="help notext" onclick="Modal.open('configuration-mode-help', {showClose: true, title: $T('CConfiguration-title-About configuration mode'), width: 600, height: 400});">{{tr}}Help{{/tr}}</button>

    {{if $config_mode === 'Standard'}}
      <button class="change" onclick="toggleAlternativeMode('alt');">
        Param�trer le mode Alternatif
      </button>
    {{else}}
      <button class="change" onclick="toggleAlternativeMode('std');">
        Param�trer le mode Standard
      </button>
    {{/if}}
</div>

{{* todo: Translate that *}}
<div id="configuration-mode-help" style="display: none;">
  <div style="padding: 5px;">
    <div class="small-info">
      <p>
        Le mode de configuration d�signe la fa�on dont sont charg�es les configurations.
        <br />
        Ce mode diff�re selon la nature de l'instance, c'est-�-dire son <strong>r�le (Production, Formation ou Qualification)</strong>.
      </p>

      <dl>
        <dt>Mode Standard</dt>
        <dd>
          Utilis� par la <strong>Production</strong>.
          <br />
          Ce Mode est le comportement par d�faut des configurations : une configuration n'a qu'une seule valeur.
        </dd>

        <dt>Mode Alternatif</dt>
        <dd>
          Utilis� par la <strong>Formation</strong> ou la <strong>Qualification</strong>.
          <br />
          Ce Mode permet de saisir deux valeurs pour une configuration : la valeur standard et la valeur alternative.
          Quand une instance travaille en Mode Alternatif, lors du chargement d'une configuration, celle-ci va regarder si une valeur alternative a �t� param�tr�e pour cette configuration.
          Si c'est le cas, alors la valeur alternative sera retourn�e. Sinon, la valeur standard de la configuration sera utilis�e.
        </dd>
      </dl>

      <div class="small-warning">
        Il n'est pas possible de modifier le Mode d'une instance.
      </div>

      <p>
        Il est donc possible de param�trer les configurations Alternatives depuis l'application.
        <br />
        Cela permet de param�trer les futures configurations de la Qualification depuis la Production puis de proc�der � un remplacement de base de donn�es sans pour autant perdre les configurations souhait�es.
      </p>
    </div>
  </div>
</div>