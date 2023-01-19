#Proc�dure de transfert de patients entre deux Mediboard
*Cette proc�dure explique comment effectuer l'export puis l'import d'une s�lection de patients d'une base  Mediboard (ou TAMM) vers 
une autre base Mediboard (ou TAMM).*

##Pr�paration
La premi�re �tape consiste � param�trer la correspondance entre les �tablissements, services, blocs, utilisateurs, cabinets.
Pour ce faire il faut exporter l'�tablissement que l'on souhaite, avec le group_id optionnel (le mettre si pas l'�tablissement 
courant) : 


[m=etablissement&raw=exportObject[&group_id=XXX]](index.php?m=etablissement&raw=exportObject)


Ensuite, l'importer sur l'instance qui va accueillir les donn�es :


[m=etablissement&tab=vw_import_group](index.php?m=etablissement&tab=vw_import_group)


Suivre les instructions pour importer / faire correspondre les �lements souhait�s (pour un cabinet : les utilisateurs, pour un 
�tablissement : les services, le bloc et les utilisateurs, etc).

##Export de la base patient

[m=patients&tab=vwExportPatients](index.php?m=patients&tab=vwExportPatients)


  - Choisir les praticiens pour lesquels ont veut faire l'import, ou cocher **"Tous les praticiens"** si c'est une base compl�te.
  - Le volet S�jours permet de g�nerer un PDF (sous forme de CFile) pour chaque s�jour, qui reprend la majorit� des donn�es. 
  Ne pas l'utiliser pour un transfert de cabinet.
  
  - Le volet Patients permet d'exporter les dossiers patients qui ont eu des consultations ou s�jours avec les m�decins s�l�ctionn�s,
   vers un rep�rtoire qu'il faut indiquer.
   
  - Le format d'export est le suivant :
    - Un r�pertoire dat� du jour de l'export
    - Qui contient un fichier XML de toutes les donn�es du patient (donn�es administratives, ant�c�dents, consultations, s�jour, etc)
    - Un r�pertoire avec tous les fichiers li�s (sauf si la case **"Ne pas copier les fichiers utilisateurs"** est coch�e, ce qui est 
    utile quand on export une base enti�re, et qu'on effectue la copie de tout le r�pertoire des CFile).

##Import de la base

[m=patients&tab=vw_import_patients](index.php?m=patients&tab=vw_import_patients)


L'import des patients se fait en indiquant le r�pertoire dat� du jour de l'export dans le champ **"R�pertoire source"**, mais aussi 
le r�pertoire des fichiers (si la case **"Ne pas copier les fichiers utilisateurs"** a �t� coch�e lors de l'export).

L'option **"Mettre � jour les donn�es en plus de les ins�rer"** permet de mettre � jour les patients d�j� import�s.

Il est possible de reprendre un import en cours avec les options de Pas et d'ID patient.
