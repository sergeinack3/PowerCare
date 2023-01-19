# Export de données au format XML d'OpenXtrem          

##1. Présentation du format d'export

L'export d'une base de données pour un ou plusieurs praticiens au format XML d'OpenXtrem est composé de plusieures parties :
- Un dossier par patient nommé "CPatient-XXX" avec "XXX" l'identifiant du patient contenant un fichier XML avec toutes les informations du patient ainsi que les documents de ce patient.
- Un fichier de description des différents éléments exportés nommé "classes_description.csv"

Les patients exportés sont ceux ayant eu au moins une consultation ou un séjour avec au moins un des praticiens sélectionnés dans l'intervalle de date choisi (si aucun intervalle de date alors il faut juste que le patient ait eu un jour une consultation ou un séjour avec un des praticiens).
Pour les différents éléments liés au patient plusieurs cas particuliers vont définir si on doit les exporter ou non :
- **Séjour** : Un séjour est exporté si toutes les conditions suivantes sont remplies :
    - Le responsable du séjour est un des praticiens sélectionnés.
    - La date d'entrée du séjour (entrée réelle si renseignée, sinon entrée prévue) est dans les bornes de dates renseignées.
- **Consultation** : Une consultation est exportée si toutes les conditions suivantes sont remplies :
    - S'il s'agit d'une consultation contenue dans un séjour, elle sera exportée si le séjour est exporté (les conditions suivantes ne sont pas vérifiées).
    - Le praticien responsable de la consultation est un des praticiens sélectionnés.
    - La date de la consultation est dans les bornes de dates renseignées
- **Intervention** : Une intervention est exportée si toutes les conditions suivantes sont remplies :
    - S'il s'agit d'une intervention contenue dans un séjour, elle sera exportée si le séjour est exporté (les conditions suivantes ne sont pas vérifiées).
    - Le chirurgien de l'intervention est un des praticiens sélectionnés.
    - La date de l'intervention est dans les bornes de dates renseignées.
- **Événements patients** : Un événement patient est exporté si toutes les conditions suivantes sont remplies :
    - Le praticien responsable de l'événement est un des praticiens sélectionnés.
    - La date de l'événement est dans les bornes de dates renseignées.
- **Actes CCAM / NGAP** : Un acte est exporté si l'exécutant de l'acte est un des praticiens sélectionnés.
- **Factures** : Une facture est exportée si le praticien ayant créé la facture est un des praticiens sélectionnés.
- **Fichiers et compte-rendus** : Un fichier ou un compte-rendu est exporté si sa cible (consultation, séjour, patient, ?) est exportée quel que soit la date de création du document.    

##2. Présentation d'un dossier pour un patient

Un dossier pour un patient va avoir la hiérarchie de sous dossiers suivante :
- Dossier "CPatient-XXX"
    - Fichier "export.xml"
    - Dossier "CConsultation"
        - Dossier "YYYY"
            - Fichier lié à la consultation CConsultation-YYY
            - Autre fichier lié à la consultation CConsultation-YYY
            - ...
        - Dossier "WWW"
            - Fichier lié à la consultation CConsultation-WWW
    - Dossier "CSejour"
        - Dossier "YYY"
            - Fichier lié au séjour CSejour-YYY

Le fichier "export.xml" va contenir au format XML toutes les informations exportées pour le patient. Vous trouverez dans le fichier "classes_description.csv" (décrit dans la partie 3 de ce document)  la liste des éléments exportés ainsi que pour chaque élément la liste des champs de cet élément et les requêtes xPath permettant de retrouver ces éléments au sein du fichier "export.xml".

Les différents fichiers rattachés aux patients sont présents dans des sous-dossiers suivant leur contexte.
Les compte-rendu sont des productions documentaires sous forme de HTML présentes dans le fichier "export.xml". Pour chaque compte-rendu on trouve un fichier qui lui est lié. Ce fichier correspond à une version PDF du compte-rendu HTML.
Lors de l'exportation des séjours on génère le dossier de soins sous forme d'un document PDF qui est exporté sous forme d'un fichier et lié au séjour.
                  
##3. Présentation du fichier CSV de description des champs

Le fichier CSV "classes_description.csv" présent dans le dossier de l'export décrit de manière technique les différents types d'objets exportés et tous leurs champs.

Les colonnes de ce CSV sont :
- **Traduction de la classe** : Nom traduit du type d'objet.
- **Nom de la classe** : Nom interne du type d'objet.
- **Nom du champ** : Nom interne du champ comme il sera présent dans l'export XML.
- **Traduction du champ** : Nom traduit du champ tel qu'il est affiché dans l'application.
- **Description du champ** : Description du champ pour donner plus d'informations que son nom.
- **Propriété du champ** : Propriété interne du champ décrivant le type de données qu'il contient :
    - **bool** : Champ booléen pouvant valoir 1 ou 0.
    - **code** : Chaîne de caractères numériques avec une vérification particulière. Le type de code est indiqué dans le reste de la propriété (insee, rib, ...)
    - **color** : Couleur en hexadécimal.
    - **currency** : Nombre à virgule représentant une valeur monétaire.
    - **date, dateTime, time et birthDate** : Champ de type date, date et heure, heure ou date de naissance. Le format est YYYY-MM-DD HH:ii:ss.
    - **email** : Chaîne de caractères représentant une adresse email.
    - **enum, set** : Champ de type liste. Un enum peut avoir une seule valeur dans la liste, un set peut avoir plusieurs valeurs de cette liste. Les valeurs possibles sont données par la partie list de la propriété.
    - **float** : Nombre à virgule.
    - **html** : Texte long étant un texte HTML.
    - **num** : Nombre.
    - **numchar** : Chaîne de caractères numérique pouvant avoir des zéro à gauche.
    - **phone** : Chaîne de caractères représentant un numéro de téléphone (10 chiffres).
    - **ref** : Le champ est une référence vers un autre objet. La partie class de la propriété indique vers quel type d'objet cette référence est.
    - **str** : Champ de type chaîne de caractères.
    - **text** : Chaîne de caractères longue.
- **Propriété SQL du champ** : Représentation SQL du champ en base de données.
- **Requête xPath pour accéder au champ** : Requête xPath permettant de récupérer le champ au sein du fichier "export.xml".




