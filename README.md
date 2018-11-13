# Esutil
Helpful utility for different development tasks.


## Scaffolding for Magento 2

* Generate new module:
```
php esutil.phar magento2/scaffold --type="{Namespace}\{Module}"
```
* Generate frontend controller
```
php esutil.phar magento2/scaffold --type="{Namespace}\{Module}\Controller\{ControllerName}\{Action}"
```
* Generate admin controller
```
php esutil.phar magento2/scaffold --type="{Namespace}\{Module}\Controller\Adminhtml\{ControllerName}\{Action}"
```
* Generate setup scripts
```
php esutil.phar magento2/scaffold --type="{Namespace}\{Module}\Setup"
```
* Generate model (with resource and collection)
```
php esutil.phar magento2/scaffold --type="{Namespace}\{Module}\Model\{ModelName}"
```


## Build from source
Command line utility for different tasks

Build phar file (in php.ini it should be allowed to write phar archives):
```
php build.php
```

This command creates esutil.phar archive in the current directory.

# Bugs and Feedback
For bugs, questions and discussions please contact us from https://www.eleanorsoft.com/

# Thanks to:
* [Eugene Polischuk](https://github.com/epolish) - idea of the scaffolding module and consulting