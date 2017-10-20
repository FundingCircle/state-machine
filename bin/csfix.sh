#!/bin/bash

MASTER_MERGE_BASE=($(git merge-base origin/master HEAD))
COMMIT_RANGE="$MASTER_MERGE_BASE..HEAD"
IFS=$'\n'; COMMIT_SCA_FILES=($(git diff --name-only --diff-filter=ACMRTUXB "${COMMIT_RANGE}")); unset IFS
./vendor/bin/php-cs-fixer fix --config=.php_cs.dist -v --using-cache=no --path-mode=intersection "${COMMIT_SCA_FILES[@]}"
