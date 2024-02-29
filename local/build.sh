#!/bin/bash

composer install --no-dev
npm run plugin-zip
composer install