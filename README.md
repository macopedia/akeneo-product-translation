# Akeneo Product Attributes Translator

Akeneo's extension adding new mass edit type "Translate attributes".

### How to download and install the connector:

Install composer dependency

```shell
composer require piotrmus/akeneo-product-translation
```

register bundle in `config/bundles.php`

```php

return [
    \Piotrmus\Translator\PiotrmusTranslatorBundle::class => ['dev' => true, 'test' => true, 'prod' => true],
];

```

define Google Cloud Translate API Key in `.env` file:

```dotenv
GOOGLE_API_KEY=yourapikey
```

### Features:

With Akeneo Product Attributes Translator you can make mass edit job to translate multiple text attributes from one
channel and one locale to target channel and target locale.

Extension uses [Google Cloud Translation API](https://cloud.google.com/translate)

### Requirements:

* Akeneo PIM >= 4.x