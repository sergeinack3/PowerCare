# Export de donn�es au format XML d'OpenXtrem          

##1. Pr�sentation du format d'export

L'export d'une base de donn�es pour un ou plusieurs praticiens au format XML d'OpenXtrem est compos� de plusieures parties :
- Un dossier par patient nomm� "CPatient-XXX" avec "XXX" l'identifiant du patient contenant un fichier XML avec toutes les informations du patient ainsi que les documents de ce patient.
- Un fichier de description des diff�rents �l�ments export�s nomm� "classes_description.csv"

Les patients export�s sont ceux ayant eu au moins une consultation ou un s�jour avec au moins un des praticiens s�lectionn�s dans l'intervalle de date choisi (si aucun intervalle de date alors il faut juste que le patient ait eu un jour une consultation ou un s�jour avec un des praticiens).
Pour les diff�rents �l�ments li�s au patient plusieurs cas particuliers vont d�finir si on doit les exporter ou non :
- **S�jour** : Un s�jour est export� si toutes les conditions suivantes sont remplies :
    - Le responsable du s�jour est un des praticiens s�lectionn�s.
    - La date d'entr�e du s�jour (entr�e r�elle si renseign�e, sinon entr�e pr�vue) est dans les bornes de dates renseign�es.
- **Consultation** : Une consultation est export�e si toutes les conditions suivantes sont remplies :
    - S'il s'agit d'une consultation contenue dans un s�jour, elle sera export�e si le s�jour est export� (les conditions suivantes ne sont pas v�rifi�es).
    - Le praticien responsable de la consultation est un des praticiens s�lectionn�s.
    - La date de la consultation est dans les bornes de dates renseign�es
- **Intervention** : Une intervention est export�e si toutes les conditions suivantes sont remplies :
    - S'il s'agit d'une intervention contenue dans un s�jour, elle sera export�e si le s�jour est export� (les conditions suivantes ne sont pas v�rifi�es).
    - Le chirurgien de l'intervention est un des praticiens s�lectionn�s.
    - La date de l'intervention est dans les bornes de dates renseign�es.
- **�v�nements patients** : Un �v�nement patient est export� si toutes les conditions suivantes sont remplies :
    - Le praticien responsable de l'�v�nement est un des praticiens s�lectionn�s.
    - La date de l'�v�nement est dans les bornes de dates renseign�es.
- **Actes CCAM / NGAP** : Un acte est export� si l'ex�cutant de l'acte est un des praticiens s�lectionn�s.
- **Factures** : Une facture est export�e si le praticien ayant cr�� la facture est un des praticiens s�lectionn�s.
- **Fichiers et compte-rendus** : Un fichier ou un compte-rendu est export� si sa cible (consultation, s�jour, patient, ?) est export�e quel que soit la date de cr�ation du document.    

##2. Pr�sentation d'un dossier pour un patient

Un dossier pour un patient va avoir la hi�rarchie de sous dossiers suivante :
- Dossier "CPatient-XXX"
    - Fichier "export.xml"
    - Dossier "CConsultation"
        - Dossier "YYYY"
            - Fichier li� � la consultation CConsultation-YYY
            - Autre fichier li� � la consultation CConsultation-YYY
            - ...
        - Dossier "WWW"
            - Fichier li� � la consultation CConsultation-WWW
    - Dossier "CSejour"
        - Dossier "YYY"
            - Fichier li� au s�jour CSejour-YYY

Le fichier "export.xml" va contenir au format XML toutes les informations export�es pour le patient. Vous trouverez dans le fichier "classes_description.csv" (d�crit dans la partie 3 de ce document)  la liste des �l�ments export�s ainsi que pour chaque �l�ment la liste des champs de cet �l�ment et les requ�tes xPath permettant de retrouver ces �l�ments au sein du fichier "export.xml".

Les diff�rents fichiers rattach�s aux patients sont pr�sents dans des sous-dossiers suivant leur contexte.
Les compte-rendu sont des productions documentaires sous forme de HTML pr�sentes dans le fichier "export.xml". Pour chaque compte-rendu on trouve un fichier qui lui est li�. Ce fichier correspond � une version PDF du compte-rendu HTML.
Lors de l'exportation des s�jours on g�n�re le dossier de soins sous forme d'un document PDF qui est export� sous forme d'un fichier et li� au s�jour.
                  
##3. Pr�sentation du fichier CSV de description des champs

Le fichier CSV "classes_description.csv" pr�sent dans le dossier de l'export d�crit de mani�re technique les diff�rents types d'objets export�s et tous leurs champs.

Les colonnes de ce CSV sont :
- **Traduction de la classe** : Nom traduit du type d'objet.
- **Nom de la classe** : Nom interne du type d'objet.
- **Nom du champ** : Nom interne du champ comme il sera pr�sent dans l'export XML.
- **Traduction du champ** : Nom traduit du champ tel qu'il est affich� dans l'application.
- **Description du champ** : Description du champ pour donner plus d'informations que son nom.
- **Propri�t� du champ** : Propri�t� interne du champ d�crivant le type de donn�es qu'il contient :
    - **bool** : Champ bool�en pouvant valoir 1 ou 0.
    - **code** : Cha�ne de caract�res num�riques avec une v�rification particuli�re. Le type de code est indiqu� dans le reste de la propri�t� (insee, rib, ...)
    - **color** : Couleur en hexad�cimal.
    - **currency** : Nombre � virgule repr�sentant une valeur mon�taire.
    - **date, dateTime, time et birthDate** : Champ de type date, date et heure, heure ou date de naissance. Le format est YYYY-MM-DD HH:ii:ss.
    - **email** : Cha�ne de caract�res repr�sentant une adresse email.
    - **enum, set** : Champ de type liste. Un enum peut avoir une seule valeur dans la liste, un set peut avoir plusieurs valeurs de cette liste. Les valeurs possibles sont donn�es par la partie list de la propri�t�.
    - **float** : Nombre � virgule.
    - **html** : Texte long �tant un texte HTML.
    - **num** : Nombre.
    - **numchar** : Cha�ne de caract�res num�rique pouvant avoir des z�ro � gauche.
    - **phone** : Cha�ne de caract�res repr�sentant un num�ro de t�l�phone (10 chiffres).
    - **ref** : Le champ est une r�f�rence vers un autre objet. La partie class de la propri�t� indique vers quel type d'objet cette r�f�rence est.
    - **str** : Champ de type cha�ne de caract�res.
    - **text** : Cha�ne de caract�res longue.
- **Propri�t� SQL du champ** : Repr�sentation SQL du champ en base de donn�es.
- **Requ�te xPath pour acc�der au champ** : Requ�te xPath permettant de r�cup�rer le champ au sein du fichier "export.xml".




