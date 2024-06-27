# SkillSpark
____

## Introduction

This project is an open-source skill-learning web application built with Symfony

## Table of content
- [Introduction](#introduction)
- [Table of content](#table-of-content)
- [Installation](#installation)
- [Usage](#usage)
- [Features](#features)
- [Dependencies](#dependencies)
- [Configuration](#configuration)
- [Documentation](#documentation)
- [Troubleshooting](#troubleshooting)
- [Contributors](#contributors)

## Installation

To install and set up SkillSpark, follow these steps:

### Prerequisites
- PHP 8 (or higher)
- Composer
- Web Server (e.g.: Apache, Nginx)
- Database (e.g.: MySQL, MariaDB, PostgreSQL)

### Installation Steps

1. Clone the repository:
```bash
git clone https://github.com/JeremyDrr/SkillSpark
```

2. Install Dependencies:
```bash
composer install
```

3. Set up environment variables:
```bash
cp .env .env.local
```

4. Database setup:
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

5. Run the application:
```bash
php bin/console server:start
```

## Usage
To use SkillSpark, open your web browser and navigate to `http://localhost:8000` \
You can interact with the various features and functionalities provided by the application.

## Features
- User Authentication and Authorisation
- CRUD operations for managing courses
- Administration Panel

## Dependencies
SkillSpark relies on the following dependencies: 
- Symfony
- Doctrine ORM
- Twig Template Engine
- Slugify (from Cocur)
- Stripe-PHP API (from Stripe)

## Configuration
Configuration settings can be adjusted in the `.env` file. Make sure to set the appropriate values for your environment, such as database connection details and mail server settings.

## Documentation
For detailed documentation on how to use and extend the application, refer to the [official Symfony documentation](https://symfony.com/doc/current/index.html). \
Furthermore, all functions are documented withing SkillSpark using PHPDoc comments.

## Troubleshooting
If you encounter any issues during the installation or usage of this project, check the following:
- Ensure all prerequisites are met.
- Verify that your environment variables are correctly set.
- Check the Symfony logs for any error messages (`var/log/dev.log`)

## Contributors
- [Eng. Jérémy DURRIEU](https://www.linkedin.com/in/jeremy-durrieu)