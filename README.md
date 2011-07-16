# Doctrine 2 OXM Bundle

Doctrine OXM is a PHP 5.3 project for PHP object to XML mapping that provides support for persisting the XML to a file system via common Doctrine techniques.  


## Instaliation

### Register annotations.

```php
AnnotationRegistry::registerFile(__DIR__.'/../vendor/doctrine-oxm/lib/Doctrine/OXM/Mapping/Driver/DoctrineAnnotations.php');
```