dokuwiki-autheid
======================

Dokuwiki plugin providing client authentication to Estonian National ID card

# Summary

# Prerequisites

## Apache Configuration

In addition to all the other SSL directives (SSLCertificateFile, SSLCertificateKeyFile, SSLCACertificateFile, etc.) you'll need to require client certificates:

```
    SSLVerifyClient require
    SSLVerifyDepth 2
    SSLOptions +StdEnvVars +ExportCertData
```
# Installation

## Automatically

You can install this by providing the URL to your Dokuwiki's Plugin Manager - https://github.com/voime/dokuwiki-autheid/zipball/master

## Manually

Unpack the plugin to DOKUWIKI_ROOT/lib/plugins/

Ensure that DOKUWIKI_ROOT/lib/plugins/autheid/* is readable by Apache.

# Configuration

Ensure that the authtype is set to autheid in conf/local.php or conf/local.protected.php:

```
$conf['authtype'] = 'autheid';
```
