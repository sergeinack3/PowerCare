#!/bin/bash

delete_locks() {
  if [ "$#" -lt 2 ]; then
    echo "Expected 2 parameters, $# given"
    echo "\t<instance_path>: Path of the instance"
    echo "\t<file_pattern_to_delete>: Pattern of the lock file to delete"
    return 0
  else
    lockFiles=$1/$2
    i=0
    for fileToDelete in $lockFiles
    do
      rm -f $fileToDelete
      i=$((i+1))
    done
    echo "Cleansing done, $i files deleted"
  fi
}

if [ "$#" -lt 2 ]; then
  echo "Usage: $0"
  echo "  <instance_path>: Path of the MB instance"
  echo "  <file_pattern_to_delete>: Pattern of the lock file to delete (partial files name will be wilcarded"

  exit 1
fi
INSTANCE_PATH=$1
PATTERN_TO_DELETE=$2*
MB_LOCKS_PATH=${INSTANCE_PATH}/tmp/locks

numberOfFilesToDelete=$(ls -l ${MB_LOCKS_PATH}/${PATTERN_TO_DELETE}|wc -l)

echo "There is $numberOfFilesToDelete file(s) to delete in $MB_LOCKS_PATH"
read -p "Do you want to continue?[y/N] " yn

case $yn in
  [Yy]*) delete_locks $MB_LOCKS_PATH $PATTERN_TO_DELETE;;
  *) echo "Cancelled";;
esac