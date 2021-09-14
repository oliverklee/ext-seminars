#!/bin/bash
composer update --with-dependencies
composer dumpautoload --optimize
find . -name ".git" -exec rm -rf {} \;
