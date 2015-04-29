Bitmart is an online marketplace that has builtin cryptocurrency payment processing, currently it supports:

* bitcoin
* litecoin
* ppcoin
* dogecoin

Prerequisites
* sphinxsearch - http://www.sphinxsearch.com
  * sphinx is used to query the categories directly from memory
  * when compiling sphinx from source manually, you need to enable --with-pgsql as the database is postgresql

* hybridauth - http://hybridauth.sourceforge.net
  * to support social login, please configure the credentials for each login provider such as facebook/google using <project_root>/kohana/application/config/hybridauth.php

* kohana - http://www.kohanaframework.org
  * this system is built based on kohana framework, you shall have some basic understanding of the framework
