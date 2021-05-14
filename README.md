# mojang-api
Simple and easy class to use

## Installation
```bash
composer require davidoc26/mojang-api
```

## Usage
#### Get UUID by username
```php
$uuid = MojangAPI::getUuid('Test'); // d8d5a9237b2043d8883b1150148d6955
```

#### Get username names history
```php
$uuid = MojangAPI::getUuid('Test');
$users = MojangAPI::getNameHistory($uuid);

foreach ($users as $user) {
    echo $user->getName();
    echo $user->getChangedToAt();
}
```

#### Render user head
```php
$url = MojangAPI::getSkinUrl($uuid);
$head = MojangAPI::renderHead($url, 300); // the second argument is the size of head
echo "<img src='$head' alt='head'>";
```
