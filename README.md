# GDELT PHP examples

This repository contains some simple scripts to access [GDELT](http://www.gdeltproject.org/) from PHP.

## Dependency: Google Cloud library

These examples depend on the presence of the Google Cloud library.

Install it with

    composer require google/cloud

## Google Cloud accound key file

Whenever you see "GDELT example-59bcb4241a57.json" in code (Constants::ACCOUNT_KEY_FILE), replace this with your own Google Cloud account JSON key.
You can obtain one by creating an account at https://console.cloud.google.com/
