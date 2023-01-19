#!/bin/bash

BASH_PATH=$(dirname $0)
. $BASH_PATH/utils.sh
MB_PATH=$(cd $BASH_PATH/../; pwd);

purge_thumbs() {
  path_to_thumbs=$1
  days_before_purge=$2
  filter=$3

  echo "Calculating number of thumbs files before purge..."
  nb_files_before=$(find $path_to_thumbs -$filter +$days_before_purge -print|wc -l)

  echo "Purging following files.."

  find $path_to_thumbs -atime +365 -delete -print
  echo "Calculating number of thumbs after purge..."
  nb_files_after=$(find $path_to_thumbs -atime +365 -print|wc -l)
  nb_purged_files=$(expr $nb_files_before - $nb_files_after)

  echo "Number of files before: $nb_files_before"
  echo "Number of files after: $nb_files_after"
  echo "Number of purged files: $nb_purged_files"

  echo "Done"
}
########
# Purge the php thumbs older than 1 year
########

echo "PHP Thumbs Purger"

if [ "$#" -lt 1 ]
then
  echo "Usage: $0 <path_to_thumbs> options"
  echo " <path_to_thumbs> path to the thumbs directory"
  echo " -d [days before purge]"
  echo " -c [use creation time filter]"
  exit 1
fi

path_to_thumbs=$1
days_before_purge=365
filter='atime'
args=$(getopt d:c $*)

if [ $? != 0 ] ; then
  echo "Invalid argument. Check your command line"; exit 0;
fi

set -- $args

for i; do
  case "$i" in
    -d) days_before_purge=$2; shift 2;;
    -c) filter='ctime'; shift;;
    --) shift ; break ;;
  esac
done

echo $days_before_purge
REGEX="(\.\.)+"
PHPTHUMB_REGEX="phpthumb(/)?$"
if [[ $path_to_thumbs =~ $REGEX && ! $path_to_thumbs =~ "/phpthumb$" ]]; then
  echo "[ERROR] - Cannot perform the purge on a relative path as it could leads to undefined and dangerous behaviors!"
  exit 1
fi

if ! [[ $path_to_thumbs =~ $PHPTHUMB_REGEX ]]; then
  echo "[ERROR] - Cannot perform the purge as the last directory is not named phpthumbs and could leads to dangerous bevahior"
  exit 1
fi

echo "Purging thumbs file older than $days_before_purge days using $filter filter..."

purge_thumbs $path_to_thumbs $days_before_purge $filter

