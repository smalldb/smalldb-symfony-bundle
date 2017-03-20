Smalldb Symfony Bundle
======================

Symfony bundle for Smalldb.

See https://smalldb.org/


Features
--------

Smalldb Symfony Bundle integrates [libSmalldb](/libsmalldb) into Symfony
framework, including the 
[Security subsystem](http://symfony.com/doc/current/security.html).
It also provides convenient configuration and optional REST-API.


Installation
------------

Add the bundle into your `composer.json`:

```json
{
    "require": {
        "smalldb/smalldb-symfony-bundle": "*"
    }
}
```

Then register the bundle in your `app/AppKernel.php`:

```php?start_inline
class AppKernel extends \Symfony\Component\HttpKernel\Kernel
{
	public function registerBundles()
	{
		return [
			// ...
			new Smalldb\SmalldbBundle\SmalldbBundle(),
			// ...
		];
	}
}
```

Finally, configure the bundle — `app/config/config.yml`:

```yml
smalldb:
    smalldb:
        base_dir: '%kernel.root_dir%/../src/AppBundle/StateMachines'
        cache_disabled: false
    flupdo:
        driver: mysql
        host: ~
        port: ~
        database: ~
        username: ~
        password: ~
        log_query: false
        log_explain: false
    auth:
        class: Smalldb\StateMachine\Auth\CookieAuth
```

… and enable authentication listener in `app/config/security.yml`:

```yml
security:
    firewalls:
        main:
            smalldb: ~
```

REST API can be unabled using predefined routes — `app/config/routing.yml`:

```yml
smalldb:
        resource: "@SmalldbBundle/Resources/config/routing.yml"
```


Usage
-----

Smaldb Symfony bundle registers `JsonDirBackend` as `smalldb` service.

Therefore you may use `$this->get('smalldb')` to retrieve Smalldb backend in
your controllers, or better inject it using `@smalldb` identifier in the
configuration files.


Documentation
-------------

See https://smalldb.org/doc/smalldb-symfony-bundle/master/


License
-------

The most of the code is published under Apache 2.0 license. See [LICENSE](doc/license.md) file for details.


Contribution guidelines
-----------------------

Project's primary repository is hosted at https://git.frozen-doe.net/smalldb/smalldb-symfony-bundle,
feel free to submit issues there or create merge requests.


