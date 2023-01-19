#!/bin/sh

BASH_PATH=$(dirname $0)
. $BASH_PATH/utils.sh
MB_PATH=$(cd $BASH_PATH/../; pwd);

########
# Backups database on a daily basis
########

announce_script "Database daily backup"

if [ "$#" -lt 5 ]
then 
  echo "Usage: $0 <method> <username> <password> <databases> <backup_path> options"
  echo " <method> is hotcopy or dump method"
  echo " <username> to access database"
  echo " <password> authenticate user"
  echo " <databases> to backup, eg mediboard. Separate by ',' to backup several databases"
  echo " <backup_path> is the backup path, eg /var/backup"
  echo " Options:"
  echo "   [-r <tmp directory>] specify a temporary directory to do the backup before compress it"
  echo "   [-t <time>] is time in days before removal of files, default 7"
  echo "   [-b ] to create mysql binary log index"
  echo "   [-l <login>] user:pass login to send a mail when diskfull is detected"
  echo "   [-f <lock_file>] lock file path"
  echo "   [-c <passphrase>] passphrase to encrypt the archive"
  echo "   [-e <cryptage>] cryptage method to use"
  echo "   [-h] do not check mysqlhotcopy command"
  exit 1
fi

login=''
time=7
binary_log=0
lock=''
tmpDir=''
passphrase=''
cryptage='aes-128-cbc'
args=$(getopt t:l:f:c:e:r:bh $*)
check_hotcopy=1

if [ $? != 0 ] ; then
  echo "Invalid argument. Check your command line"; exit 0;
fi

set -- $args

for i; do
  case "$i" in
    -t) time=$2; shift 2;;
    -l) login=$2; shift 2;;
    -f) lock=$2; shift 2;;
    -c) passphrase=$2; shift 2;;
    -e) cryptage=$2; shift 2;;
    -b) binary_log=1; shift;;
    -h) check_hotcopy=0; shift;;
    -r) tmpDir=$2; shift 2;;
    --) shift ; break ;;
  esac
done

method=$1
username=$2
password=$3
databases=$(echo $4|xargs -n1 -d,|sort -u|xargs) # Remove duplicates from databases
BACKUP_PATH=$5
DATETIME=$(date +%Y-%m-%dT%H-%M-%S)

# Save the old IFS because of the find command
old_ifs=$IFS
IFS=$','

info_script "Backuping '$databases' databases"

# Create lock file
if [ -n "$lock" ]
then
  touch $lock
fi

# Make backup path
force_dir $BACKUP_PATH

# Make database path
for database in $databases; do
  BASE_PATH=${BACKUP_PATH}/$database-db
  force_dir $BASE_PATH
done

# Check free disk space
mysql_conf=$(find /etc/ -name my.cnf 2>/dev/null|head -n 1)
if [ -z "$mysql_conf" ]
then
  check_errs 2 "MySQL configuration file not found"
fi

mysql_data_root=$(grep datadir $mysql_conf|tr -s ' '|cut -d"=" -f 2|sed -e 's/^[ \t]*//')

if [ -z $mysql_data_root ]
then
  mysql_data_root="/var/lib/mysql"
fi

available_size=$(df -k $BACKUP_PATH|tail -n 1|sed -r 's/\s+/\ /g'|cut -d" " -f 4)
available_size=$((available_size))

needed_size=0

for database in $databases; do
  mysql_data_base="$mysql_data_root/$database"

  database_size=$(du -k $mysql_data_base|tail -n 1|sed -r 's/\s+/\ /g'|cut -d" " -f 1)

  # Expanded size (database + tarball)
  needed_size=$((needed_size+database_size*3/2))
done

if [ $available_size -lt $needed_size ]
then
  if [ -n "$login" ]
  then
    info_script "Send a mail using $login login"
    # Name of the instance of mediboard
    instance=$(cd $MB_PATH ; pwd);
    instance=${instance##*/}
    wget "http://localhost/${instance}/?login=${login}&m=system&a=ajax_send_mail_diskfull"
  fi
  check_errs 2 "Needed space ($needed_size) exceeds available space ($available_size)"
fi

info_script "Needed space ($needed_size) less than available space ($available_size)"

# Flush query cache to prevent fragmentation
mysql --user=$username --password=$password --execute="FLUSH QUERY CACHE"
check_errs $? "Failed to flush query cache" "Query cache flushed"

## Make MySQL method

# removes previous hotcopy/dump if something went wrong
for database in $databases; do
  BASE_PATH=${BACKUP_PATH}/$database-db
  cd $BASE_PATH
  rm -Rf $database
done

for database in $databases; do
  BASE_PATH=${BACKUP_PATH}/$database-db
  hotcopyDir=$BASE_PATH
  if [ -n "$tmpDir" ]
  then
    hotcopyDir=$tmpDir
  fi

  cd $hotcopyDir

  case $method in
    hotcopy)
      mysqlhotcopy --quiet -u $username -p $password $database $hotcopyDir

      if [ $check_hotcopy -eq 1 ]; then
        check_errs $? "Failed to create MySQL hot copy for database $database" "MySQL hot copy done for database $database !"
      fi

      if [ $binary_log -eq 1 ]; then
        mysql --user=$username --password=$password $database < $BASH_PATH/mysql_show_master_status.sql > $BACKUP_PATH/binlog-${DATETIME}.index
        check_errs $? "Failed to create MySQL Binary log index" "MySQL Binary log index done!"
      fi
      ;;
    dump)
      mysqldump --opt -u ${username} -p${password} $database > $database.sql
      check_errs $? "Failed to create MySQL dump for database $database" "MySQL dump done for database $database !"
      ;;
    *)
      echo "Choose hotcopy or dump method"

      if [ -n "$lock" ]
      then
        rm $lock
      fi
      exit 1
      ;;
  esac
done

# rotating files older than n days, all files if 0
filter=""
if [ $time -ne 0 ]; then
  filter="-ctime +$time"
fi

for database in $databases; do
  BASE_PATH=${BACKUP_PATH}/$database-db
  basename="$database*.tar.gz"
  if [ -n "$passphrase" ]; then
    basename="$basename.aes"
  fi
  # Restore the old IFS during the find command execution
  IFS=$old_ifs
  find ${BASE_PATH} -name "$basename" $filter -exec /bin/rm '{}' ';'
  IFS=$','
  check_errs $? "Failed to rotate files for database $database" "Files rotated for database $database"
done

# Compress archive and remove files

# Make the tarball
for database in $databases; do
  case $method in
    hotcopy)
      result=$database/
      if [ -n "$tmpDir" ]
      then
        mv $tmpDir/$database $BASE_PATH
      fi
      ;;
    dump)
      result=$database.sql
      if [ -n "$tmpDir" ]
      then
        mv $tmpDir/$result $BASE_PATH
      fi
      ;;
    *)
      exit 1
      ;;
  esac

  BASE_PATH=${BACKUP_PATH}/$database-db


  cd $BASE_PATH

  tarball=$database-${DATETIME}.tar.gz
  tar cfz $tarball $result
  check_errs $? "Failed to create backup tarball" "Tarball packaged for database $database !"

  # Crypt the tarball
  if [ -n "$passphrase" ]; then
    openssl $cryptage -salt -in $tarball -out $tarball.aes -k $passphrase
    check_errs $? "Failed to crypt the archive" "Archive crypted for database $database !"
    # create a symlink
    cp -s -f $tarball.aes $database-latest.tar.gz.aes
    check_errs $? "Failed to create symlink of archive crypted" "Symlink of crypted archive created for database $database !"
    rm $tarball
    check_errs $? "Failed to delete non-crypted archive" "Archive non-crypted deleted for database $database !"
  else
    # create a symlink
    cp -s -f $tarball $database-latest.tar.gz
    warn_errs $? "Failed to create symlink" "Symlink created for database $database !"
  fi

  # Remove temporary files
  rm -Rf $result
  check_errs $? "Failed to clean MySQL files" "MySQL files cleaning done for database $database !"

  if [ -n "$lock" ]
  then
    rm $lock
  fi

  # Write event file
  event=$MB_PATH/tmp/monitevent.txt

  echo "#$(date +%Y-%m-%dT%H:%M:%S)" >> $event
  echo "<strong>$database</strong> base backup: <strong>$method</strong> method" >> $event
done