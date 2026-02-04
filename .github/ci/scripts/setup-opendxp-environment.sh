#!/bin/bash

set -eu

cp -r .github/ci/files/bin bin
cp -r .github/ci/files/kernel kernel
cp .github/ci/files/.env ./

chmod 755 bin/console
