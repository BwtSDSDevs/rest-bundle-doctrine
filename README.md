rest-bundle-doctrine
===============
Thanks to **Philip Washington Sorst** for the inital [Project](https://github.com/dontdrinkandroot/rest-bundle.php) to fork from.

Updated Project to work with Doctrine ORM 3 and Symfony 7.1.*

## About

This Symfony Bundle automatically generates Routes for all registered Doctrine ORM Entities.

It generates get, search, update, insert and delete routes.

## Install

Install via composer

`composer require niebvelungen/rest-bundle-doctrine`

Enable the Bundle in the `config/bundles.php` file of your Symfony project:

`return [
Niebvelungen\RestBundleDoctrine\DdrRestBundle::class => ['all' => true]
];`

Register the routes in the `config/routes.xml` file of your Symfony project:
<?xml version="1.0" encoding="UTF-8" ?>
`<routes xmlns="http://symfony.com/schema/routing"
xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
xsi:schemaLocation="http://symfony.com/schema/routing
https://symfony.com/schema/routing/routing-1.0.xsd">
..
<import resource="." type="rest_json"/>
..
</routes>`

