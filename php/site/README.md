# Happn Hack website

This folder contains a tiny website to play with the API, with the following features:
* User information (self & others)
* List of notifications
* List of conversations & messages
* Accept user (aka like) / cancel accept
* List of accepted users
* Reject user (aka block) / cancel reject
* List of rejected users

This website allows some operations that are not available in the official application ('cancel accept' and 'list of accepted users'), and is more convenient for browsing.

## Installation

Just upload the content of the `php` folder to your webserver, and open the `/site/index.php` main page in your browser.

The initial data is the Facebook token, that should be stored in the `fb.json` configuration file at the `XXXXX` placeholder:
```
{"fb_token":"XXXXX"}
```

The code only requires PHP engine with CURL activated, and has no dependency to any other PHP library (as it aims to demonstrate the hacking, not to be another Happn fancy application). It shall have write access to the installation folder to allow the creation of the `auth.json` data file, where the authentication data is saved between two HTTP requests.
