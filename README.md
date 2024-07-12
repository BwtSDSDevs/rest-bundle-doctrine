rest-bundle-doctrine
===============
Thanks to **Philip Washington Sorst** for the inital [Project](https://github.com/dontdrinkandroot/rest-bundle.php) to fork from.

Updated Project to work with Doctrine ORM 3 and Symfony 7.1.*

## About

This Symfony Bundle automatically generates Routes for all registered Doctrine ORM Entities.

It generates [get](#get), [search](#search), [update](#update), [insert](#insert) and [delete](#delete) routes.

## Install

Install via composer
```
composer require sdsdev/rest-bundle-doctrine
```

Enable the Bundle in the `config/bundles.php` file of your Symfony project:

```php
return [
    ...
    SdsDev\RestBundleDoctrine\DdrRestBundle::class => ['all' => true]
];
```

Register the routes in the `config/routes.xml` file of your Symfony project:
```xml
<?xml version="1.0" encoding="UTF-8" ?>
<routes xmlns="http://symfony.com/schema/routing"
xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
xsi:schemaLocation="http://symfony.com/schema/routing
https://symfony.com/schema/routing/routing-1.0.xsd">
...
    <import resource="." type="rest_json"/>
</routes>
```

## Usage

After fully installing there should be routes available for all entities that are registered in the Doctrine ORM

Routes will return or take data in JSON format.

If you want to see a list of all routes use:
```
bin/console debug:router
```

The entityName will get mapped to its plural: so a User entity will become:
`/api/doctrine/search/users`

### Search
```
    /api/doctrine/search/{entityName}
```
*Method: **POST***

#### Request Options

Body Schema
```JSON
{
    "associations":[
        "...",
        "..."
    ],
    "filter":[
        {
            "type": "equals",
            "field": "id",
            "value": 1
        }
    ],
    "sort":[
        {
            "field": "id",
            "order": "ASC"
        }
    ]
}
```

#### Associations
You can freely add associations that will be loaded **with** the requested entity. 

Associations need to be given in snake case and as a string. 

Associations can be up to 2 paths deep.

Example:

User has a connected Table UserRole and we want to load it.

Our Body would look like this:
```JSON
{
    "associations":[
        "user_role"
    ]
}
```

###### Nested associations

For **nested associations** you can chain them with `entity.entity`

e.g. if UserRoles had a connected Table permissions and we want to load them too

Our Body would look like this:
```JSON
{
    "associations":[
        "user_role.permissions"
    ]
}
```


#### Filter

Available Filters
* `equals`
* `not_equals`
* `gt` (greater than)
* `lt` (less than)

To get for e.x. a User with the id 1 our body would look like this

Our Body would look like this:
```JSON
{
    "filter":[
        {
            "type": "equals",
            "field": "id",
            "value": 1
        }
    ],
}
```

If we want to filter for a User with a specific Role we can also do that

```JSON
{
    "filter":[
        {
            "type": "equals",
            "field": "userRole.id",
            "value": 1
        }
    ],
}
```

This will return all users that have the UserRole with id 1.

If we want to load and filter the association simultaneously we can give the filter a `mode` parameter.

As of writing this doc only `"mode": "full"` is supported everything else will not have any impact.

So if we want to get a User that has the UserRole id 1 and the corresponding UserRole our Body looks like this:

```JSON
{
    "associations":[
        "user_role"
    ],
    "filter":[
        {
            "type": "equals",
            "field": "userRole.id",
            "value": 1,
            "mode": "full"
        }
    ],
}
```

If we want to get all UserRoles but still only Users with the UserRole.id 1 we can just remove the `"mode":"full"` parameter.

#### Sorting

We can define sortings to our entities by giving the request body a `sort` array.

Sample Body
```JSON
{
    "sort":[
        {
            "field": "id",
            "order": "ASC"
        }
    ]
}
```

Available sort orders
* `ASC`
* `DESC`


### Get
```
    /api/doctrine/get/{entityName}/{id}
```
*Method: **GET***

Returns an Entity with the given name and id.

Get can also use Associations see [Associations](#associations) above.

### Insert 
```
    /api/doctrine/insert/{entityName}
```

*Method: **POST***

Body is a JSON representation of the object that you want to create.

If we assume you have a User Entity
User has id, name, username, additionalName, a Country (ManyToOne Entity) and we got a country with id 1 in there.

Your Body to insert a new User would look like this

```JSON
{
    "id": 1,
    "name": "Test User",
    "username": "TestAccount",
    "additionalName": null,
    "country": 1
}
```
(If id is autoincrement you have to delete the `"id"` field in the json)

**If a mandatory foreign key is not set the api will return an Error!**

Returns 201 if successful

### Update
```
    /api/doctrine/update/{entityName}/{id}
```

*Method: **PUT/PATCH***

If we assume you have a User Entity
User has id, name, username and we want to update the username

Your Body to update would look like this

```JSON
{
    "username": "New User Name",
}
```

Returns 200 if successful

### Delete
```
    /api/doctrine/delete/{entityName}/{id}
```
*Method: **DELETE***

Deletes an entity with the given id.

Will return an Error if the id does not exist.

Returns 204 if successful

