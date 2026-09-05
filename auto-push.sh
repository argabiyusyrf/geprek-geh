#!/usr/bin/env bash
cd /var/www/html/geprek-geh || exit 1

if [ -z "$(git status --porcelain)" ]; then
    exit 0
fi

export GIT_SSH_COMMAND="ssh -o StrictHostKeyChecking=accept-new"
git add -A
git commit -m "auto: $(date +'%Y-%m-%d %H:%M:%S')"
git push origin main