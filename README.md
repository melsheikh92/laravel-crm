<p align="center">
<a href="https://hamzahllc.com"><img src="https://hamzahllc.com/assets/logo.png" alt="ProvenSuccess CRM"></a>
</p>

<p align="center">
<a href="https://hamzahllc.com"><img src="https://img.shields.io/badge/ProvenSuccess-CRM-blue" alt="ProvenSuccess CRM"></a>
<a href="https://hamzahllc.com"><img src="https://img.shields.io/badge/License-MIT-green" alt="License"></a>
</p>

![enter image description here](https://raw.githubusercontent.com/krayin/temp-media/master/dashboard.png)

## Topics

1. [Introduction](#introduction)
2. [Documentation](#documentation)
3. [Requirements](#requirements)
4. [Installation & Configuration](#installation-and-configuration)
5. [License](#license)
6. [Security Vulnerabilities](#security-vulnerabilities)

### Introduction

[ProvenSuccess CRM](https://hamzahllc.com) is a hand tailored CRM framework built on some of the hottest opensource technologies
such as [Laravel](https://laravel.com) (a [PHP](https://secure.php.net/) framework) and [Vue.js](https://vuejs.org)
a progressive Javascript framework.

**Free & Opensource Laravel CRM solution for SMEs and Enterprises for complete customer lifecycle management.**

**ProvenSuccess CRM is developed and maintained by [hamzah LLC](https://hamzahllc.com)**

**For support and inquiries, please visit: [hamzah LLC](https://hamzahllc.com)**

It packs in lots of features that will allow your business to scale in no time:

-   Descriptive and Simple Admin Panel.
-   Admin Dashboard.
-   Custom Attributes.
-   Built on Modular Approach.
-   Email parsing via Sendgrid.
-   Check out [these features and more](https://hamzahllc.com/features/).

**For Developers**:
Take advantage of two of the hottest frameworks used in this project -- Laravel and Vue.js -- both of which have been used in ProvenSuccess CRM.

### Documentation

#### ProvenSuccess Documentation

For detailed documentation, please visit [hamzahllc.com](https://hamzahllc.com)

### Requirements

-   **SERVER**: Apache 2 or NGINX.
-   **RAM**: 3 GB or higher.
-   **PHP**: 8.1 or higher
-   **For MySQL users**: 5.7.23 or higher.
-   **For MariaDB users**: 10.2.7 or Higher.
-   **Node**: 8.11.3 LTS or higher.
-   **Composer**: 2.5 or higher

### Installation and Configuration

##### Execute these commands below, in order

```
composer create-project
```

-   Find **.env** file in root directory and change the **APP_URL** param to your **domain**.

-   Also, Configure the **Mail** and **Database** parameters inside **.env** file.

```
php artisan provensuccess-crm:install
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

ProvenSuccess CRM is a fully open-source CRM framework which will always be free under the [MIT License](LICENSE).

### Security Vulnerabilities

Please don't disclose security vulnerabilities publicly. If you find any security vulnerability in ProvenSuccess CRM then please email us: support@hamzahllc.com.

---

**ProvenSuccess CRM** - Developed by [hamzah LLC](https://hamzahllc.com)
