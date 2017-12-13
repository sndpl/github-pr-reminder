#!/usr/bin/env bash
#docker-compose exec cli php github-pr-reminder "${@:2}"
docker run -it --rm --name github-pr-reminder -v "$PWD":/src -w /src php:7.1-cli php github-pr-reminder --dry-run



