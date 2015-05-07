# Bitmart

Built on top of [Kohana framework](http://www.kohanaframework.org), Bitmart is an online marketplace that allows anyone to buy and sell physical or digital goods, using cryptocurrencies as payment methods. At the moment the following cryptocurrencies are supported:
* bitcoin
* litecoin
* ppcoin
* dogecoin

Some features include:
* built-in Escrow - Order paid with cryptocurrency will trigger the built-in escrow process.

* instant digital delivery - once enabled, digital goods such as activation code or serial number can be delivered to the buyer in text form or url after payment has been made

* custom shipping table and tax modules

# Prerequisites


* [sphinx](http://www.sphinxsearch.com) is used to query the categories directly from memory. When compiling sphinx from source manually, you need to enable --with-pgsql as the database is postgresql

* As Bitmart uploads listing photos directly to Amazon s3-compatible storage, you will need to provide credentials and a bucket name from such service provider (eg, Amazon s3, dreamobjects, Google cloud storage)

* Cryptocurrency daemon - If you want to accept Bitcoin and Litecoin as payment methods, you have to compile and run both bitcoind and litecoind daemons as background services.

* [openexchangerates account](http://www.openexchangerates.org/) - convert one currency to another via JSON API

# Installation
* Download and extract the source code
* Enable url rewrite, for nginx: rewrite ^(.+)$ /index.php?kohana_uri=$1 last;
* Install Bitmart database from the dump file - **project_root**/database/bitmart.sql.tar.gz
* Customize the settings: **project_root**/kohana/config/*
  * config/hybridauth.php - configure credentials of each service provider (eg: Facebook/Google/Yahoo) in order to support social login
  * config/database.php - Postgresql database credentials
  * config/general.php - Configure site name, base url, s3-compatible credentials, cryptocurrencies settings and more.
* After you have got sphinxsearch up and running, copy and modify **project_root**/database/sphinx.conf accordingly.
* Edit the settings in  **project_root**/database/populate_category.php and run the script to copy category records to sphinx index.
* Compile/download and run cryptocurrency daemon. For example, if you plan to accept Bitcoin, make sure bitcoind is running. It's needed so that when a new transaction hits the wallet, Bitmart will be notified via a transaction hash and process the transaction accordingly. You can turn the following command into a service,
```
/opt/crypto/bitcoind -datadir=/opt/crypto/bitcoin -daemon -blocknotify="curl http://**your_site**/callback/block/btc/%s" -walletnotify="curl http://**your_site**/callback/wallet/btc/%s"
```
* Configure a cron job to run **project_root**/database/convert_currency.php, it will fetch the currency prices via openexchangerates JSON API and update **project_root**/kohana/application/general.php accordingly

* The default login is: username = admin, password = admin

# Known Issues / Todo
* **project_root**/kohana/application/general.php contains hardcoded cryptocurrency exchange rate, it's supposed to be updated via third-party exchange such as bittrex and cryptsy.

![Alt text](/screenshots/ss1.jpg?raw=true "Cryptocurrency page")
![Alt text](/screenshots/ss2.jpg?raw=true "Settings page")



