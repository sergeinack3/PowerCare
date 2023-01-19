Requis :
--------------
- MS SQL environnement (le client ou le serveur MS SQL doivent être installé)
- sqlcmd doit être dans le PATH
- MSSQL to MySQL doit être installé
- m2sagent.exe doit être dans le path
- Les droits d'écriture dans la base de données cible
- Installation des extensions "php_sqlsrv_56_ts.dll" et "php_pdo_sqlsrv_56_ts.dll" parmis les extensions de PHP (ne pas oublier de les ajouter dans php.ini)

Arguments :
-----------
* -c, --config	: Chemin du fichier de configuration
* --restore	: Si on a accès directement à la base de données MSSQL mettre à 1 (inutile de restaurer la BDD)
* -p, --path 		: Chemin du fichier d'initialisation
* --dump			: Chemin où le fichier dump sera créé
	
MS SQL restore :
----------------
--> Pour récupérer les noms des fichiers logiques : sqlcmd -S localhost -E -Q "RESTORE FILELISTONLY FROM DISK='Chemin du .bak'"
- host			: Adresse du serveur MSSQL dans lequel il faut restorer la base de données
- db_name		: Nom de la base de donnée à restorer
- bak_path		: Chemin vers le fichier .bak
- data_logique	: Chemin du fichier logique contenant les données de la BD
- path_data		: Chemin du fichier physique où sauvegarder les données de la BD 
- log_logique	: Chemin du fichier logique contenant les logs
- path_log		: Chemin du fichier physique où sauvegarder les logs
	
Convertion :
-------------
- host_mysql		: Adresse du serveur MySQL cible(peut être vide, default localhost)
- username_mysql	: Nom de l'utilisateur MySQL à utiliser
- password_mysql	: Mot de passe de l'utilisateur (peut être vide)
- port				: Port du serveur MySQL (peut être vide, default 3306)
- host_mssql		: Adresse du serveur MSSQL source (peut être vide, default localhost)
- username_mssql	: Nom d'utilisateur MSSQL (peut être vide, default connexion via l'annuaire windows)
- password_mssql	: Mot de passe de l'utilisateur MSSQL (peut être vide, default connexion via l'annuaire windows)
- db_target			: Nom de la base de données MySQL où écrire les données (Création de la base si elle n'existe pas)
- db_source			: Nom de la base de données MSSQL source

	
Connaitre la version de MS SQL du backup : sqlcmd -S localhost -E -Q "RESTORE HEADERONLY FROM DISK='chemin du .bak'"
	- softwareVersionMajor	: 8		-> 2000
							  9		-> 2005
							  10	-> 2008
							  11	-> 2012
							  12	-> 2014
							  13	-> 2016
