# mojang-api

Simple and easy class to use

[![Latest Stable Version](http://poser.pugx.org/davidoc26/mojang-api/v)](https://packagist.org/packages/davidoc26/mojang-api) 
[![Total Downloads](http://poser.pugx.org/davidoc26/mojang-api/downloads)](https://packagist.org/packages/davidoc26/mojang-api) 
[![License](http://poser.pugx.org/davidoc26/mojang-api/license)](https://packagist.org/packages/davidoc26/mojang-api) 
[![PHP Version Require](http://poser.pugx.org/davidoc26/mojang-api/require/php)](https://packagist.org/packages/davidoc26/mojang-api)

## Installation

```bash
composer require davidoc26/mojang-api
```

## Usage

#### Get status of Mojang services

```php
use Davidoc26\MojangAPI\MojangAPI;

$mojangAPI = new MojangAPI();

$services = $mojangAPI->apiStatus();

foreach ($services as $service) {
    echo $service->getName(); // the name of service
    echo $service->getStatus(); // can be green/yellow/red
}
```

#### Get UUID by username

```php
$uuid = $mojangAPI->getUuid('Test'); // d8d5a9237b2043d8883b1150148d6955
```

#### Get username names history

```php
$uuid = $mojangAPI->getUuid('Test');
$users = $mojangAPI->getNameHistory($uuid);

foreach ($users as $user) {
    echo $user->getName();
    echo date('d M | Y', $user->getChangedToAt()); // Be careful! getChangedToAt() can return null
}
```

#### Render user head

```php
$url = $mojangAPI->getSkinUrl($uuid);
$head = $mojangAPI->renderHead($url, 300); // the second argument is the size of head
echo "<img src='$head' alt='head'>";
```

#### Authentication

```php
$user = $mojangAPI->authenticate('email','password');

$user->getName();
$user->getUuid();
$user->getAccessToken();
$user->nameAvailability($newName); 

// You can also render the head of the current player.
$user->renderHead($size);
```

#### Check name availability

```php
$isAvailable = $mojangAPI->nameAvailability($username, $token); // bool
```
