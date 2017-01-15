#!/bin/bash
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )";
cd $DIR;
if [[ $# -eq 0 ]] ; then
    php "www/index.php"
else
    php "www/index.php" "$@"
fi
