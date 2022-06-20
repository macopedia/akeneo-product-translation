# Akeneo Product Attributes Translator

Akeneo's extension adding new mass edit type "Translate attributes".

### How to download and install the connector:

Install composer dependency

```shell
composer require macopedia/akeneo-product-translation
```

register bundle in `config/bundles.php`

```php

return [
    \Macopedia\Translator\MacopediaTranslatorBundle::class => ['dev' => true, 'test' => true, 'prod' => true],
];

```

define Google Cloud Translate API Key in `.env` file:

```dotenv
GOOGLE_API_KEY=yourapikey
```

add new job instance

```shell
bin/console akeneo:batch:create-job internal update_product_translations mass_edit update_product_translations '{}' 'Translate product'
```

### Features:

With Akeneo Product Attributes Translator you can make mass edit job to translate multiple text attributes from one
channel and one locale to target channel and target locale.

Extension uses [Google Cloud Translation API](https://cloud.google.com/translate)

### Requirements:

* Akeneo PIM >= 4.x