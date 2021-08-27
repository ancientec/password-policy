# `password-policy` 

PHP helper function to generate password policies and validate against password.

PHP Version:
[https://github.com/ancientec/password-policy ](https://github.com/ancientec/password-policy)

JS Version (for frontend and backend):
[https://github.com/ancientec/password-policy-js ](https://github.com/ancientec/password-policy-js)

## Features
- Cache and validate multiple policies by assigning different names
- Customiziable error strings, multilingual possible
- Custom error handling
- Custom validation
- Detail errors
- Compatiable javascript version for consistent frontend and backend result

## Install

```shell
> composer require ancientec/password-policy
```
## Develop & Unit Test

```shell
> git clone https://github.com/ancientec/password-policy.git
> cd password-policy
> composer install
> vendor/bin/phpunit
>
```

## Usage & Example

```php

use Ancientec\PasswordPolicy\PasswordPolicy;

$policy = ["LengthMin" => 6, //minimum length of password
      "LengthMax" => 12, //maximum length of password
      "CharDigitMin" => 1, //minimum number of digits
      "CharUpperMin" => 1,//minimum number of upper case characters
      "CharLowerMin" => 1,//minimum number of lower case characters
      "CharSpecial" => "~!@#$%^&*()-=_+",//defination of special characters
      "CharSpecialMin" => 1,//minimum number of special characters
      "MustContain" => ['1','a'], //must contain strs, case sensitive
      "MustNotContain" => ['admin','password'],//must not contain strs, case sensitive
      "CustomValidate" => function($password) { return "";}, //return error string if false, return "" if true
      ];

/**
 * @param array $policy
 * @param array $errStrings
 * @param string $name
 *
 */
$passwordPolicy = new PasswordPolicy($policy, [], "policy_admin");

//return false:
$passwordPolicy->isValid("password"); //false

//return true:
$passwordPolicy->isValid("Password123!"); //true

//return empty array if the password is passed:
$passwordPolicy->Validate("Password123!"); // empty []

//return array of error strings:
$passwordPolicy->Validate(""); 
/* result:
["ERR_LengthMin" => "minimum length of 6",
        "ERR_LengthMax" =>  "maximum length of 12",
        "ERR_CharDigitMin" => "at least 1 of digit(s)",
        "ERR_CharUpperMin" => "at least 1 of upper case character",
        "ERR_CharLowerMin" => "at least 1 of lower case character",
        "ERR_CharSpecial" => "at least 1 of special character ~!@#$%^&*()-=_+",
        "ERR_MustContain" => ["must contain 1","must contain a"],
        "ERR_All" => [
              "maximum length of 12",
              "at least 1 of digit(s)",
              "at least 1 of upper case character",
              "at least 1 of lower case character",
              "at least 1 of special character ~!@#$%^&*()-=_+",
              "must contain 1",
              "must contain a"
        ]
        ];
*/

```
The returned error array can be processed by using string index or by numeric index in ERR_All

## Multiple Policies

```php

//only check minimum length
$passwordPolicy = new PasswordPolicy(["LengthMin" => 6], [], "policy_admin");

//create a new instance
$passwordPolicyUser = new PasswordPolicy(["LengthMin" => 12], [], "policy_user");

//or use static::method
PasswordPolicy::registerPolicy(["LengthMin" => 32, "LengthMax" => 32], [], "policy_api");

//policy is still policy_admin:
$passwordPolicy->isValid("Password123!");//return true

//change current policy name to policy_user
$passwordPolicy->setPolicyName("policy_user");

//policy_user validate:
$passwordPolicy->isValid("Password123!");//return false

//assign policy name to validate:
$passwordPolicy->isValid("Password123!", "policy_admin");//return true

//policy is policy_user
$passwordPolicyUser->isValid("Password123!");//return false

//assign policy name to validate:
$passwordPolicy->isValid("Password123!", "policy_user");//return false

//assign policy name to validate:
$passwordPolicy->isValid("Password123!", "policy_api");//return false

```

## Customizible Error Strings
```php

$errorStrings = PasswordPolicy::getErrorStringsDefault();
/* default strings:

["ERR_LengthMin" => "minimum length should be {0}",
        "ERR_LengthMax" =>  "maximum length should be {0}",
        "ERR_CharDigitMin" => "at least {0} of digit(s)",
        "ERR_CharUpperMin" => "at least {0} of upper case character",
        "ERR_CharLowerMin" => "at least {0} of lower case character",
        "ERR_CharSpecial" => "at least {0} of special character {1}",
        "ERR_MustContain" => "must contain {0}",
        "ERR_MustNotContain" => "must not contain {0}",
        "ERR_NoDefinedPolicies" => "Missing defined policies",
        ]
*/
$errorStrings["ERR_LengthMin"] = "Minimum length must be {0}";

$passwordPolicy = new PasswordPolicy(
      ["LengthMin" => 6, "LengthMax" => 6],$errorStrings, "policy_user");
//alertnatively:
$passwordPolicy->setErrorStringsWithName($errorStrings, "policy_user");

$passwordPolicy->validate("");
/* result:
[
      "ERR_LengthMin" => "minimum length must be 6",
      "ERR_All" => ["minimum length must be 6"],
]
*/

```

## Customizible Error String Functions
In case if you need to translate error strings dynamically during runtime. Note that all error strings should be covered if you define your own function:
```php

/*
$error : string, type of error
$values: string[], policy requirement
*/
$ErrorStringFormat = function($error, $values) {

      $myLanguageStrings = ["ERR_LengthMin" => "minimum length must be {0}",
        "ERR_LengthMax" =>  "maximum length must be {0}",
        "ERR_CharDigitMin" => "at least {0} of digit(s)",
        "ERR_CharUpperMin" => "at least {0} of upper case character",
        "ERR_CharLowerMin" => "at least {0} of lower case character",
        "ERR_CharSpecial" => "at least {0} of special character {1}",
        "ERR_MustContain" => "must contain {0}",
        "ERR_MustNotContain" => "must not contain {0}",
        "ERR_NoDefinedPolicies" => "Missing defined policies",
        ];
      switch($error) {
            case 'ERR_CharSpecial':
                return sprintf($myLanguageStrings[$error],$values[1],$values[0]);
            case 'ERR_NoDefinedPolicies':
                return $myLanguageStrings[$error];
            default:
            return sprintf($myLanguageStrings[$error],$values[1],$values[0]);
      }
}

$policy = [
      "LengthMin" => 6, 
      "LengthMax" => 6,
      "ErrorStringFormat" => $ErrorStringFormat];

$passwordPolicy = new PasswordPolicy(
      ["LengthMin" => 6, "LengthMax" => 6],$errorStrings);

$passwordPolicy->validate("");
/* result:
[
      "ERR_LengthMin" => "minimum length must be 6",
      "ERR_All" => ["minimum length must be 6"],
]
*/

```

## Custom Validation
Provide your own validation.
```php
$customValidate = function($password) {
      //password is not ok:
      if(strpos($password, "abc") !== 0) {
            return "password should prefix abc";
      }

      //password is ok:
      return "";
};

$passwordPolicy = new PasswordPolicy([
      "LengthMin" => 6,
      "CustomValidate" => $customValidate
]);

$passwordPolicy->isValid("password"); //return false

$passwordPolicy->validate("password");
/*
result:
[
      "ERR_CustomValidate" => "password should prefix abc",
      "ERR_All" => ["password should prefix abc"],
]
*/

$passwordPolicy->isValid("abcPassword"); //return true
$passwordPolicy->validate("abcPassword"); //result: []

```

##Static Methods
Register new policies
```php
/*
$policy: array,
$errorStrings : array, optional
$name: string, optional
*/
PasswordPolicy::registerPolicy(/*$policy*/, /*$errorStrings*/, /*$name*/);
```

Set error strings
```php
/*
$errorStrings : array,
$name: string, optional
*/
PasswordPolicy::setErrorStringsWithName(/*$errorStrings*/, /*$name*/);
```

Get All registered policies
```php
/*
return array
*/
PasswordPolicy::getPolicies();
```

Get policy
```php
/*
return first policy
*/
PasswordPolicy::getPolicy();

/*
return policy by name
*/
PasswordPolicy::getPolicy("default");
```

Get default policy definition
```php
/*
return ["LengthMin" => 8,
        "LengthMax" => 16,
        "CharDigitMin" => 1,
        "CharUpperMin" => 1,
        "CharLowerMin" => 1,
        "CharSpecial" => "~!@#$%^&*()-=_+",
        "CharSpecialMin" => 1,
        "MustContain" => [],
        "MustNotContain" => [],
        ];
*/
PasswordPolicy::getPolicyDefault();
```

Get default error strings definition
```php
/*
return ["ERR_LengthMin" => "minimum length should be {0}",
        "ERR_LengthMax" =>  "maximum length should be {0}",
        "ERR_CharDigitMin" => "at least {0} of digit(s)",
        "ERR_CharUpperMin" => "at least {0} of upper case character",
        "ERR_CharLowerMin" => "at least {0} of lower case character",
        "ERR_CharSpecial" => "at least {0} of special character {1}",
        "ERR_MustContain" => "must contain {0}",
        "ERR_MustNotContain" => "must not contain {0}",
        "ERR_NoDefinedPolicies" => "Missing defined policies",
        ];
*/
PasswordPolicy::getErrorStringsDefault();
```

## License

MIT