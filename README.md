<p align="center">
<a href="https://provensuccess.com"><img src="https://via.placeholder.com/400x100/3D3552/EFEFEF?text=ProvenSuccess" alt="ProvenSuccess"></a>
</p>

<p align="center">
<a href="https://packagist.org/packages/krayin/laravel-crm"><img src="https://poser.pugx.org/krayin/laravel-crm/d/total.svg" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/krayin/laravel-crm"><img src="https://poser.pugx.org/krayin/laravel-crm/v/stable.svg" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/krayin/laravel-crm"><img src="https://poser.pugx.org/krayin/laravel-crm/license.svg" alt="License"></a>
</p>

## Topics

1. [Introduction](#introduction)
2. [Requirements](#requirements)
3. [Installation & Configuration](#installation-and-configuration)
4. [License](#license)
5. [Security Vulnerabilities](#security-vulnerabilities)

### Introduction

**ProvenSuccess** is a powerful CRM platform built on modern technologies including [Laravel](https://laravel.com) and [Vue.js](https://vuejs.org).

**Free & Opensource CRM solution for SMEs and Enterprises for complete customer lifecycle management.**

ProvenSuccess helps you manage your customer relationships effectively with:

-   Intuitive and Simple Admin Panel
-   Comprehensive Dashboard with Analytics
-   Custom Attributes and Fields
-   Built on Modular Architecture
-   Email Integration and Parsing
-   Advanced Lead Management
-   Pipeline Management
-   Activity Tracking
-   Quote Generation
-   And much more...

**For Developers**:
Built with Laravel and Vue.js, ProvenSuccess offers a modern, maintainable codebase that's easy to customize and extend.

### Requirements

-   **SERVER**: Apache 2 or NGINX
-   **RAM**: 3 GB or higher
-   **PHP**: 8.1 or higher
-   **For MySQL users**: 5.7.23 or higher
-   **For MariaDB users**: 10.2.7 or Higher
-   **Node**: 8.11.3 LTS or higher
-   **Composer**: 2.5 or higher

### Installation and Configuration

##### Execute these commands below, in order

```
composer create-project
```

-   Find **.env** file in root directory and change the **APP_URL** param to your **domain**.

-   Also, Configure the **Mail** and **Database** parameters inside **.env** file.

```
php artisan krayin-crm:install
```

**To execute ProvenSuccess**:

##### On server:

Warning: Before going into production mode we recommend you uninstall developer dependencies.
In order to do that, run the command below:

> composer install --no-dev

```
Open the specified entry point in your hosts file in your browser or make an entry in hosts file if not done.
```

##### On local:

```
php artisan route:clear
php artisan serve
```

**How to log in as admin:**

> _http(s)://example.com/admin/login_

```
email:admin@example.com
password:admin123
```

### License

ProvenSuccess is a fully open-source CRM platform which will always be free under the [MIT License](https://github.com/krayin/laravel-crm/blob/2.1/LICENSE).

### Security Vulnerabilities

Please don't disclose security vulnerabilities publicly. If you find any security vulnerability in ProvenSuccess then please email us: security@provensuccess.com.
