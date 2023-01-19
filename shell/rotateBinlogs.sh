#!/bin/sh

BASH_PATH=$(dirname $0)
. $BASH_PATH/utils.sh

if [ "$#" -lt 5 ]
then
  echo "Usage: $0 <MySQL_username> <MySQL_password> <binlogs_directory> <binlog-index_filename> <backup_directory> options"
  echo " <MySQL_username>  is the MySQL username allowed to connect, ie admin"
  echo " <MySQL_password> is the password of the MySQL user"
  echo " <binlogs_directory>  is the directory where binlogs are stored, ie /var/log/mysql"
  echo " <binlog-index_filename> is the name of the binlog-index file, ie log-bin.index"
  echo " <backup_directory> is the name of the directory where binlogs will be stored, ie /mbbackup/binlogs"
  echo " Options :"
  echo "   [-c <passphrase>] is the passphrase to encrpyt the archive"
  echo "   [-e <cryptage>]   is the cryptage method to use"
  exit 1
fi

passphrase=''
cryptage='aes-128-cbc'

args=$(getopt c:e: $*)

if [ $? != 0 ] ; then
  echo "Invalid argument. Check your command line"; exit 0;
fi

set -- $args
for i; do
  case "$i" in
    -c) passphrase=$2; shift 2;;
    -e) cryptage=$2; shift 2;;
    --) shift ; break ;;
  esac
done

##################
# Rotate binlogs #
##################

announce_script "Rotate binlogs"

# Backup destination dir
backup=$5
mkdir -p $5

# Tmp dir to compress
dir=$3
tmpdir=$3/tmp_binlogs
mkdir -p $tmpdir

info_script "Flush logs to start a new one"
# Flush logs to start a new one
mysqladmin -u $1 -p$2 flush-logs

# Links logs except the last to tmp dir
info_script "Links logs except the last to tmp dir"
index="$dir/$4"

find $dir -name "*bin.0*" -print | sort > /tmp/binlogs

last_binlog=$(tail -n 1 $index)


while read log;
do
  log_name=$(basename $log)

  # Don't process on the last binlog
  if [ "$log" = "$last_binlog" ]
  then
    info_script "Current binlog is $last_binlog : don't link"
    continue
  fi

  # Don't process if the binlog is already compressed
  if [ -n "$passphrase" ]
  then
    find_binlog=$(find $backup -name "binlogs_$log_name.tar.gz.aes")
  else
    find_binlog=$(find $backup -name "binlogs_$log_name.tar.gz")
  fi

  if [ -n "$find_binlog" ]
  then
    info_script "Binlog $log_name already compressed"
    continue
  fi

  info_script "Linking $log";
  cp $log $tmpdir/;
  check_errs $? "Failed to move $log" "$log moved to tmp dir" \;
done < /tmp/binlogs

# Copy binlog indeces to binlog backup
info_script "Copying binlog indeces to binlog backup"
cp $index $backup
check_errs $? "Failed to copy binlog indeces to binlog backup" "Binlog indeces moved to binlog backup"

# Archive binlogs
cd $tmpdir
info_script "Compress binlogs"

if [ -n "$passphrase" ]; then
  for i in $(ls *.0*)
  do
    nice -n 10 tar -vczf - $i | openssl $cryptage -salt -out $tmpdir/binlogs_$i.tar.gz.aes -k $passphrase
    check_errs $? "Failed to compress and crypt $i" "$i compressed and crypted"
    info_script "Moving compressed and crypted binlog to $backup"
    mv $tmpdir/binlogs_$i.tar.gz.aes $backup
    check_errs $? "Failed to move compressed binlog to $backup" "binlog moved to $backup"
  done
else
  for i in $(ls *.0*)
  do
    nice -n 10 tar -vczf $tmpdir/binlogs_$i.tar.gz $i
    check_errs $? "Failed to compress $i" "$i compressed"
    info_script "Moving compressed binlog to $backup"
    mv $tmpdir/binlogs_$i.tar.gz $backup
    check_errs $? "Failed to move compressed binlog to $backup" "binlog moved to $backup"
  done
fi

# Remove temp directory
info_script "Remove temp directory"
cd ..
rm -rf $tmpdir
check_errs $? "Failed to remove $tmpdir" "$tmpdir removed"

# Cleaning binlogs for 24h
info_script "Remove binlogs older than 24h in $dir"
find $dir -name "*bin.0*" -cmin +1440 -delete

# Rotate binlogs for a week
info_script "Rotating binlogs for a week in $backup"

if [ -n "$passphrase" ]; then
  find $backup -name "binlogs_*.tar.gz.aes" -mtime +7 -exec rm -f {} \;
else
  find $backup -name "binlogs_*.tar.gz" -mtime +7 -exec rm -f {} \;
fi