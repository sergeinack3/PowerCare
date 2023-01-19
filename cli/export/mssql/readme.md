Requis :
--------------
- MS SQL environnement (le client ou le serveur MS SQL doivent �tre install�)
- sqlcmd doit �tre dans le PATH
- MSSQL to MySQL doit �tre install�
- m2sagent.exe doit �tre dans le path
- Les droits d'�criture dans la base de donn�es cible
- Installation des extensions "php_sqlsrv_56_ts.dll" et "php_pdo_sqlsrv_56_ts.dll" parmis les extensions de PHP (ne pas oublier de les ajouter dans php.ini)

Arguments :
-----------
* -c, --config	: Chemin du fichier de configuration
* --restore	: Si on a acc�s directement � la base de donn�es MSSQL mettre � 1 (inutile de restaurer la BDD)
* -p, --path 		: Chemin du fichier d'initialisation
* --dump			: Chemin o� le fichier dump sera cr��
	
MS SQL restore :
----------------
--> Pour r�cup�rer les noms des fichiers logiques : sqlcmd -S localhost -E -Q "RESTORE FILELISTONLY FROM DISK='Chemin du .bak'"
- host			: Adresse du serveur MSSQL dans lequel il faut restorer la base de donn�es
- db_name		: Nom de la base de donn�e � restorer
- bak_path		: Chemin vers le fichier .bak
- data_logique	: Chemin du fichier logique contenant les donn�es de la BD
- path_data		: Chemin du fichier physique o� sauvegarder les donn�es de la BD 
- log_logique	: Chemin du fichier logique contenant les logs
- path_log		: Chemin du fichier physique o� sauvegarder les logs
	
Convertion :
-------------
- host_mysql		: Adresse du serveur MySQL cible(peut �tre vide, default localhost)
- username_mysql	: Nom de l'utilisateur MySQL � utiliser
- password_mysql	: Mot de passe de l'utilisateur (peut �tre vide)
- port				: Port du serveur MySQL (peut �tre vide, default 3306)
- host_mssql		: Adresse du serveur MSSQL source (peut �tre vide, default localhost)
- username_mssql	: Nom d'utilisateur MSSQL (peut �tre vide, default connexion via l'annuaire windows)
- password_mssql	: Mot de passe de l'utilisateur MSSQL (peut �tre vide, default connexion via l'annuaire windows)
- db_target			: Nom de la base de donn�es MySQL o� �crire les donn�es (Cr�ation de la base si elle n'existe pas)
- db_source			: Nom de la base de donn�es MSSQL source

	
Connaitre la version de MS SQL du backup : sqlcmd -S localhost -E -Q "RESTORE HEADERONLY FROM DISK='chemin du .bak'"
	- softwareVersionMajor	: 8		-> 2000
							  9		-> 2005
							  10	-> 2008
							  11	-> 2012
							  12	-> 2014
							  13	-> 2016
