#Procédure de transfert de patients entre deux Mediboard
*Cette procédure explique comment effectuer l'export puis l'import d'une sélection de patients d'une base  Mediboard (ou TAMM) vers 
une autre base Mediboard (ou TAMM).*

##Préparation
La première étape consiste à paramétrer la correspondance entre les établissements, services, blocs, utilisateurs, cabinets.
Pour ce faire il faut exporter l'établissement que l'on souhaite, avec le group_id optionnel (le mettre si pas l'établissement 
courant) : 


[m=etablissement&raw=exportObject[&group_id=XXX]](index.php?m=etablissement&raw=exportObject)


Ensuite, l'importer sur l'instance qui va accueillir les données :


[m=etablissement&tab=vw_import_group](index.php?m=etablissement&tab=vw_import_group)


Suivre les instructions pour importer / faire correspondre les élements souhaités (pour un cabinet : les utilisateurs, pour un 
établissement : les services, le bloc et les utilisateurs, etc).

##Export de la base patient

[m=patients&tab=vwExportPatients](index.php?m=patients&tab=vwExportPatients)


  - Choisir les praticiens pour lesquels ont veut faire l'import, ou cocher **"Tous les praticiens"** si c'est une base complète.
  - Le volet Séjours permet de génerer un PDF (sous forme de CFile) pour chaque séjour, qui reprend la majorité des données. 
  Ne pas l'utiliser pour un transfert de cabinet.
  
  - Le volet Patients permet d'exporter les dossiers patients qui ont eu des consultations ou séjours avec les médecins séléctionnés,
   vers un repértoire qu'il faut indiquer.
   
  - Le format d'export est le suivant :
    - Un répertoire daté du jour de l'export
    - Qui contient un fichier XML de toutes les données du patient (données administratives, antécédents, consultations, séjour, etc)
    - Un répertoire avec tous les fichiers liés (sauf si la case **"Ne pas copier les fichiers utilisateurs"** est cochée, ce qui est 
    utile quand on export une base entière, et qu'on effectue la copie de tout le répertoire des CFile).

##Import de la base

[m=patients&tab=vw_import_patients](index.php?m=patients&tab=vw_import_patients)


L'import des patients se fait en indiquant le répertoire daté du jour de l'export dans le champ **"Répertoire source"**, mais aussi 
le répertoire des fichiers (si la case **"Ne pas copier les fichiers utilisateurs"** a été cochée lors de l'export).

L'option **"Mettre à jour les données en plus de les insérer"** permet de mettre à jour les patients déjà importés.

Il est possible de reprendre un import en cours avec les options de Pas et d'ID patient.
