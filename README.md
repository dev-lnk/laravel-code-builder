# Generating laravel code from SQL table schema

[![Latest Stable Version](https://img.shields.io/packagist/v/dev-lnk/laravel-code-builder)](https://packagist.org/packages/dev-lnk/laravel-code-builder)
[![Total Downloads](https://img.shields.io/packagist/dt/dev-lnk/laravel-code-builder)](https://packagist.org/packages/dev-lnk/laravel-code-builder)
[![tests](https://github.com/dev-lnk/laravel-code-builder/workflows/tests/badge.svg)](https://github.com/dev-lnk/laravel-code-builder/actions)
[![License](https://img.shields.io/packagist/l/dev-lnk/laravel-code-builder)](https://packagist.org/packages/dev-lnk/laravel-code-builder)\
[![Laravel required](https://img.shields.io/badge/Laravel-10+-FF2D20?style=for-the-badge&logo=laravel)](https://laravel.com)
[![PHP required](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php)](https://www.php.net/manual/)

### Description
Hello Laravel users! This package allows you to generate code from the schema of your SQL table. The following entities will be generated:
- [Controller](https://github.com/dev-lnk/laravel-code-builder/blob/master/docs/examples/controller.md)
- [Model](https://github.com/dev-lnk/laravel-code-builder/blob/master/docs/examples/model.md)
- [FormRequest](https://github.com/dev-lnk/laravel-code-builder/blob/master/docs/examples/request.md)
- [DTO](https://github.com/dev-lnk/laravel-code-builder/blob/master/docs/examples/dto.md)
- [AddAction](https://github.com/dev-lnk/laravel-code-builder/blob/master/docs/examples/add_action.md)
- [EditAction](https://github.com/dev-lnk/laravel-code-builder/blob/master/docs/examples/edit_action.md)
- [Route](https://github.com/dev-lnk/laravel-code-builder/blob/master/docs/examples/route.md)
- [Form](https://github.com/dev-lnk/laravel-code-builder/blob/master/docs/examples/form.md)
- [Table](https://github.com/dev-lnk/laravel-code-builder/blob/master/docs/examples/table.md)

These examples have been generated from a table created by migration:
```php
Schema::create('products', function (Blueprint $table) {
    $table->id();
    $table->string('title')->default('Default');
    $table->text('content');
    $table->foreignIdFor(User::class)
        ->nullable()
        ->constrained()
        ->nullOnDelete()
        ->cascadeOnUpdate();
    $table->smallInteger('sort_number')->default(0);
    $table->boolean('is_active')->default(0);
    $table->timestamps();
    $table->softDeletes();
});
```
### What is this package for?
This package allows you to significantly reduce the  routine while coding and focus on developing.

### Installation
```shell
composer require dev-lnk/laravel-code-builder --dev
```
### Configuration:
Publish the package configuration file:
```shell
php artisan vendor:publish --tag=laravel-code-builder
```
### Usage
The basic command signature looks like this:
```shell
code:build {entity} {table?}
```
Let's say we want to create classes for the base table `users` based on the `User` entity. To do this you need to run the following command:
```shell
php artisan code:build User
```
You will be presented with a list of your tables, choose which table you want to generate the code based on:
```shell
 ┌ Table ───────────────────────────────────────────────────────┐
 │   ○ migrations                                             │ │
 │   ○ password_reset_tokens                                  │ │
 │   ○ products                                               │ │
 │   ○ sessions                                               │ │
 │ › ● users                                                  ┃ │
 └──────────────────────────────────────────────────────────────┘
```
You can also specify part of the table name to shorten the list
```shell
php artisan code:build User us
 ┌ Table ───────────────────────────────────────────────────────┐
 │ › ● users                                                    │
 └──────────────────────────────────────────────────────────────┘
```
If you have not specified a `generation_path` in the configuration file, you will be offered 2 options:
```shell
 ┌ Where to generate the result? ───────────────────────────────┐
 │ › ● In the project directories                               │
 │   ○ To the generation folder: `app/Generation`               │
 └──────────────────────────────────────────────────────────────┘
```
The first option will create all files according to the folders in your `app_path` directory. If a file with the same name is found, you will be prompted to replace it:
```shell
app/Models/User.php was created successfully!
...
 ┌ Controller already exists, are you sure you want to replace it? ┐
 │ Yes                                                             │
 └─────────────────────────────────────────────────────────────────┘

app/Http/Controllers/UserController.php was created successfully!
...
```
In the second option, all files will be generated in the `app/Generation` folder
```shell
app/Generation/Models/User.php was created successfully!
...
```
In the `builders` configuration you can comment out those builders that you do not want to be executed
```php
use DevLnk\LaravelCodeBuilder\Enums\BuildType;

return [
    'builders' => [
        BuildType::MODEL,
//        BuildType::DTO,
//        BuildType::ADD_ACTION,
//        BuildType::EDIT_ACTION,
//        BuildType::REQUEST,
//        BuildType::CONTROLLER,
//        BuildType::ROUTE,
//        BuildType::TABLE,
        BuildType::FORM,
    ],
    //...
];
```
You can generate certain entities using flags:
```shell
php artisan code:build user --model --request
```
Available options for the only flag:
- `--model`
- `--request`
- `--DTO`
- `--addAction`
- `--editAction`
- `--controller`
- `--route`
- `--form`
- `--table`
- `--builder` - Generates all builders specified in the `builders` configuration + your specified flag, for example:
```shell
php artisan code:build user --builders --request
```
### Documentation
- **[Relationship](https://github.com/dev-lnk/laravel-code-builder/blob/master/docs/relationship.md)**
- **[Customization](https://github.com/dev-lnk/laravel-code-builder/blob/master/docs/customization.md)**
- **[For contributors](https://github.com/dev-lnk/laravel-code-builder/blob/master/docs/for_contributors.md)**