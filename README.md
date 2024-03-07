# Paypal

Пакет позволяет производить создание планов и подписок Paypal


## Установка

````
$ composer require unetway/paypal
````

## Использование


### Параметры

- sandbox true|false
- client_id
- secret


### Создание продукта

````
use Unetway\Paypal\Paypal;

$params = [
   'sandbox' => true,
   'client_id' => '',
   'secret' => '',
];

$data = [];

$paypal = new Paypal($params);
$res = $paypal->createProduct($data);

````


### Создание плана

````
use Unetway\Paypal\Paypal;

$params = [];
$data = [];

$paypal = new Paypal($params);
$res = $paypal->createBillingPlan($data);

````


### Создание подписки

````
use Unetway\Paypal\Paypal;

$params = [];
$data = [];

$paypal = new Paypal($params);
$res = $paypal->createSubscriptions($data);

````


### Активация подписки

````
use Unetway\Paypal\Paypal;

$params = [];
$id = '';

$paypal = new Paypal($params);
$res = $paypal->activateSubscriptions($id);

````

### Приостановление подписки

````
use Unetway\Paypal\Paypal;

$params = [];
$id = '';

$paypal = new Paypal($params);
$res = $paypal->suspendSubscriptions($id);

````



### Отмена подписки

````
use Unetway\Paypal\Paypal;

$params = [];
$id = '';

$paypal = new Paypal($params);
$res = $paypal->cancelSubscriptions($id);

````


### Возобновление подписки

````
use Unetway\Paypal\Paypal;

$params = [];
$id = '';

$paypal = new Paypal($params);
$res = $paypal->reviseSubscriptions($id);

````



### Обновление цены подписки

````
use Unetway\Paypal\Paypal;

$params = [];
$data = [];
$id = '';

$paypal = new Paypal($params);
$res = $paypal->updatePricePlan($data, $id);

````




