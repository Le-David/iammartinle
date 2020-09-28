#!/usr/bin/env bash
set -euo pipefail
IFS=$'\n\t'
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
ROOT_DIR="$(dirname "$DIR")"

if [ "$CI_COMMIT_REF_NAME" = "deploy/beta" ]; then
  cp "config/config.beta.neon" "config/config.local.neon"

elif [ "$CI_COMMIT_REF_NAME" = "deploy/prod" ]; then
  cp "config/config.prod.neon" "config/config.local.neon"

else
  echo "ref $CI_COMMIT_REF_NAME not configured for deploy"
  exit 0
fi

cd "$ROOT_DIR"

rm -rf \
  .git \
  .idea \
  doc \
  frontend \
  node_modules \
  log \
  temp \
  tests

if [[ -d vendor ]]; then
  find vendor -name .DS_Store -exec rm -rf {} \;
  find vendor -regex '.*/(\.git|[Dd]oc|[Dd]ocs|[Tt]est|[Tt]ests)' -exec rm -rf {} \;
fi

mkdir -p log temp
