# Telenor Pakistan Corporate SMS API Wrapper for PHP

This composer package offers a quick Telenor Corporate SMS setup for your Laravel applications.

## Installation

Begin by pulling in the package through Composer.

```bash
composer require developifynet/telenor-php
```

## Usage

Within your controllers, you can call TelenorSMS Object and can send quick SMS.



##### For Single Number
```php
use \Developifynet\Telenor\TelenorSMS;
public function index()
{
    $SMSObj = array(
        'username' => '<PUT_YOUR_USERNAME_HERE>',   // Usually this is mobile number
        'password' => '<PUT_YOUR_PASSWORD_HERE>',   // User your password here
        'to' => '923XXXXXXXXX',                     // You can provide single number as string or an array of numbers
        'text' => '<PUT_YOUR_MESSAGE_HERE>',        // Message string you want to send to provided number(s)
        'mask' => '<PUT_YOUR_MASK_HERE>',           // Use a registered mask with Telenor
        'test_mode' => '0',                         // 0 for Production, 1 for Mocking as Test
    );
    
    $telenor = new TelenorSMS();
    $response = $telenor->SendSMS($SMSObj);
}
```

##### For Multiple Numbers
```php
use \Developifynet\Telenor\TelenorSMS;
public function index()
{
    $SMSObj = array(
        'username' => '<PUT_YOUR_USERNAME_HERE>',   // Usually this is mobile number
        'password' => '<PUT_YOUR_PASSWORD_HERE>',   // User your password here
        'to' => ['923XXXXXXXXX', '923XXXXXXXXX'],   // You can provide single number as string or an array of numbers
        'text' => '<PUT_YOUR_MESSAGE_HERE>',        // Message string you want to send to provided number(s)
        'mask' => '<PUT_YOUR_MASK_HERE>',           // Use a registered mask with Telenor
        'test_mode' => '0',                         // 0 for Production, 1 for Mocking as Test
    );
    
    $telenor = new TelenorSMS();
    $response = $telenor->SendSMS($SMSObj);
}
```

### Note
Provided numbers should start with Country code. A Pakistani number you have to write down as 923XXXXXXXXX