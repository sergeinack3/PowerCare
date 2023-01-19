{{*
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<style>
  ul {
    line-height: normal;
  }

  p {
    margin: 1em 0.2em 0.5em !important;
  }
</style>

<h1>Format de l'export CSV</h1>

<p>Le format CSV contient, en première ligne les libellés suivants, puis les données correspondant sur les lignes qui succèdent :</p>
<ul>
  <li><strong>page</strong> : numéro de la page (en partant de 0)</li>
  <li><strong>t</strong> : timestamp d'affichage de la page (en ms)</li>
  <li><strong>i</strong> : numéro de la requête dans la page (en partant de 0)</li>
  <li><strong>m</strong> : nom interne du module</li>
  <li><strong>mView</strong> : titre du module</li>
  <li><strong>a</strong> : nom interne du script dans le module</li>
  <li><strong>aView</strong> : titre du script si traduit</li>
  <li><strong>pageSize</strong> : taille de la réponse HTTP non compressée en kilo-octets</li>
  <li><strong>dbTime</strong> : temps base de données (en ms)</li>
</ul>

<p>Puis une liste de colonnes, séparées en 2 ou 3 :</p>
<ul>
  <li><strong>XXX_start</strong> : début de l'événement depuis "t", en ms</li>
  <li><strong>XXX_duration</strong> : durée de l'évènement en ms</li
  <li><strong>XXX_memory</strong> : utilisation mémoire côté serveur pour les évènements côté serveur</li>
</ul>

<p>Voici la description des XXX :</p>
<ul class="timeline">
  <li><strong class="bar-network">domainLookup</strong> : DNS (Résolution du nom de domaine (DNS))</li>
  <li><strong class="bar-network">connect</strong> : Connexion (Initialisation de la connexion)</li>
  <li><strong class="bar-network">request</strong> : Requête (Envoi de la requête)</li>
  <li><strong class="bar-server">handlerInit</strong> : Serveur init. (Initialisation du serveur)</li>
  <li><strong class="bar-server">frameworkInit</strong> : Fx init. (Initialisation du framework)</li>
  <li><strong class="bar-type-session">session</strong> : Session (Ouverture et verrou de la session)</li>
  <li><strong class="bar-server">framework</strong> : Framework (Suite du chargement du framework)</li>
  <li><strong class="bar-server">app</strong> : App. (Code applicatif (dépend de la page affichée) et construction de la page)</li>
  <li><strong class="bar-server">output</strong> : Sortie (Sortie texte (output buffer))</li>
  <li><strong class="bar-network">otherInfra</strong> : Autre infra (Autre temps, acheminement de la requête et de la page)</li>
  <li><strong class="bar-network">download</strong> : Téléchargement (Temps de téléchargement de la réponse)</li>
  <li><strong class="bar-client">domInit</strong> : Init. DOM (Temps d'initialisation de l'arbre DOM)</li>
  <li><strong class="bar-client">domLoading</strong> : Constr. DOM (Temps de construction de l'arbre DOM)</li>
  <li><strong class="bar-client">domContentLoadedEvent</strong> : Charg. DOM (Temps de l'évènement d'exécution des scripts suivant le chargement de l'arbre DOM)</li>
  <li><strong class="bar-client">loadEvent</strong> : Charg. contenu (Temps de téléchargement des contenus externes: images, etc)</li>
</ul>
