# Xentixar Validator

Xentixar Validator is a PHP validation library that provides a robust mechanism for validating data based on various rules. It includes a command-line tool for publishing configuration files and a `Validator` class for data validation.

## Features

- Data validation with rules like required, email, min, max, unique, and more.
- Customizable error messages.
- Integration with a PDO-based database for unique and existence checks.
- Command-line utility for managing configuration files.

## Installation

```sh
composer require xentixar/validator
```

## Configuration

Before using the validator, you'll need to set up configuration files.

### Publishing Configuration Files

Run the following command to publish the default configuration files to your project:

```sh
cd vendor/xentixar/validator/bin
php xentixar publish:config
```

This command will copy the configuration files to `config/vendor/validator`:

- `database.php` for database connection settings.
- `messages.php` for custom validation messages.

### Configuration Files

#### `config/vendor/validator/database.php`

Provide your database connection settings here. Example:

```php
<?php

return [
    'driver' => 'mysql',
    'host' => '127.0.0.1',
    'port' => '3306',
    'database' => 'your_database',
    'username' => 'your_username',
    'password' => 'your_password',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
];
```

#### `config/vendor/validator/messages.php`

Define custom error messages for validation rules. Example:

```php
<?php

return [
    'required' => 'The :field field is required.',
    'email' => 'The :field field must be a valid email address.',
    'min' => 'The :field field must be at least :param characters.',
    'max' => 'The :field field must not exceed :param characters.',
    'between' => 'The :field field must be between :min and :max characters.',
    'numeric' => 'The :field field must be a number.',
    'integer' => 'The :field field must be an integer.',
    'url' => 'The :field field must be a valid URL.',
    'date' => 'The :field field must be a valid date (Y-m-d).',
    'confirmed' => 'The :field confirmation does not match.',
    'same' => 'The :field field must match the :param field.',
    'unique' => 'The :field field must be unique.',
    'exists' => 'The :field field does not exist.',
    'in' => 'The :field field must be one of the following values: :values.',
    'regex' => 'The :field field format is invalid.',
    'size' => 'The :field field must be exactly :param characters.',
    'date_format' => 'The :field field does not match the format :param.',
    'file' => 'The :field field must be a valid uploaded file.',
    'mimes' => 'The :field field must be a file of type: :types.',
];
```

## Usage

### Basic Validation

Here's a quick example of how to use the `Validator` class:

```php
use Xentixar\Validator\Validator;

$validator = new Validator();

$data = [
    'email' => 'example@domain.com',
    'password' => 'password123',
];

$rules = [
    'email' => 'required|email',
    'password' => 'required|min:6',
];

if ($validator->validate($data, $rules)) {
    echo "Validation passed!";
} else {
    print_r($validator->errors());
}
```

### Error Messages

You can customize error messages in the `messages.php` configuration file. The validator will use these messages if a rule fails.

## Command-Line Utility

### Available Commands

- `publish:config`: Copies the default configuration files to the `config/vendor/validator` directory.

### Running Commands

To run a command, use:

```sh
php xentixar [command]
```

For example:

```sh
php xentixar publish:config
```

## Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository.
2. Create a feature branch (`git checkout -b feature/YourFeature`).
3. Commit your changes (`git commit -am 'Add new feature'`).
4. Push to the branch (`git push origin feature/YourFeature`).
5. Create a new Pull Request.

## License

This package is licensed under the [MIT License](LICENSE).

## Contact

For questions or support, please open an issue on [GitHub](https://github.com/xentixar/validator/issues).
