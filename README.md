OpositaTestReportBundle
=======================

Bundle to show statistics over a project ecommerce with Sylius.

## Installation

### Step 1: Install Sylius 0.18

``` bash
$ wget http://getcomposer.org/composer.phar
$ php composer.phar create-project sylius/sylius-standard path/to/install
$ cd path/to/install
$ php app/console sylius:install
```

### Step 2: Install the bundle via composer

Add manually the following line to the `composer.json` file:

``` json
{
    "require": {
        // ...
        "opositatest/report-bundle": "dev-master"
    }
}
```

### Step 3: Enable the bundle

Finally, enable the bundle in the kernel:

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new OpositaTest\Bundle\ReportBundle\OpositaTestReportBundle(),
    );
}
```

Now, go to the Report menu in the admin and you can choose some news Data Fetchers.

MIT License
-----------

License can be found [here](https://github.com/opositatest/ReportBundle/blob/master/LICENSE).

Authors
-------

The bundle was originally created by [Odiseo Team](http://odiseo.com.ar) for OpositaTest.
