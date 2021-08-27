<?php

/*
* https://github.com/ancientec/password-policy
*
* Ancientec Co., Ltd. 
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Ancientec\PasswordPolicy;

class PasswordPolicy {
    //current policy name
    public $policyName = "";
    //all policies
    static $policies = [];
    //error strings per policy
    static $errorStrings = [];

    /**
     * @param array $policy
     * @param array $errStrings
     * @param string $name
     *
     */
    function __construct($policy = [], $errStrings = [], $name = "default") {
        $this->policyName = $name;
        self::registerPolicy($policy, $errStrings, $name);
    }

    /**
     * @param array $policy
     * @param array $errStrings
     * @param string $name
     *
     */
    static function registerPolicy($policy, $errStrings = [],   $name = "default") {
        self::$policies[$name] = $policy;
        self::$errorStrings[$name] = array_merge(self::getErrorStringsDefault(), $errStrings);
    }

    /**
     * @param array $strs
     * @param string $name
     *
     */
    static function setErrorStringsWithName($strs = [], $name = "default") {
        self::$errorStrings[$name] = array_merge(self::$errorStrings[$name], $strs);
    }

    /**
     * @param string $name
     *
     */
    function setPolicyName($name) {
        $this->policyName = $name;
    }

    static function getPolicies() {
        return self::$policies;
    }

    /**
     * @param string $name
     *
     */
    static function getPolicy($name = "") {
        if($name === "" && count(self::$policies) === 1) {
            return current(self::$policies);
        }
        return !empty(self::$policies[$name]) ? self::$policies[$name] : false; 
    }
    
    static function getPolicyDefault() {
        //default policies:
        /**
         * {
         * LengthMin : 8,
         * LengthMax : 32,
         * CharDigitMin : 1,
         * CharUpperMin : 1,
         * CharLowerMin : 1,
         * CharSpecial : "~!@#$%^&*()-=_+",
         * CharSpecialMin : 1,
         * "MustContain" => [],
         * "MustNotContain" => [],
         * "CustomValidate" => function($password) : string {},
         * }
         */
        //strong:
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
    }
    static function getErrorStringsDefault() {
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
    }
    static function errorString($errorStrings, $err, $values = []) {
        switch($err) {
            case 'ERR_CharSpecial':
                return str_replace(['{0}',"{1}"], $values,$errorStrings[$err]);
            case 'ERR_NoDefinedPolicies':
                return $errorStrings[$err];
            default:
            return str_replace('{0}', $values[0],$errorStrings[$err]);
        }
    }
    function isValid($password, $policyName = "") {
        if($policyName && empty(self::$policies[$policyName])) {
            //missing defined policies
            return false;
        }
        if(!$policyName) {
            $policyName = $this->policyName;
        }
        if(empty(self::$policies[$policyName])) {
            //missing defined policies
            return false;
        }
        return empty($this->Validate($password, $policyName));
    }
    function validate($password, $policyName = "") {
        $error = [];
        $err_all = [];
        if($policyName && empty(self::$policies[$policyName])) {
            //missing defined policies
            return [
                "ERR_NoDefinedPolicies" => self::errorString(self::getErrorStringsDefault(), "ERR_NoDefinedPolicies"),
                "ERR_All" => [self::errorString(self::getErrorStringsDefault(), "ERR_NoDefinedPolicies")]
            ];
        }
        if(!$policyName) {
            $policyName = $this->policyName;
        }
        if(empty(self::$policies[$policyName])) {
            //missing defined policies
            return [
                "ERR_NoDefinedPolicies" => self::errorString(self::getErrorStringsDefault(), "ERR_NoDefinedPolicies"),
                "ERR_All" => [self::errorString(self::getErrorStringsDefault(), "ERR_NoDefinedPolicies")]
            ];
        }
        $policy = self::$policies[$policyName];
        $errStrs = self::$errorStrings[$policyName];

        if(empty($policy['ErrorStringFormat']) || !is_callable($policy['ErrorStringFormat'])) {
            $policy['ErrorStringFormat'] = function($err, $values) use ($errStrs){
                return PasswordPolicy::errorString($errStrs, $err, $values);
            };
        }
        $ErrorStringFormat = $policy['ErrorStringFormat'];
        
        //check length:
        if(!empty($policy['LengthMin']) && strlen($password) < $policy['LengthMin']) {
            $error["ERR_LengthMin"] = $ErrorStringFormat("ERR_LengthMin", [$policy['LengthMin']]); // Password length must be greater or equal than %0
            $err_all[] = $error["ERR_LengthMin"];
        }
        if(!empty($policy['LengthMax']) && strlen($password) > $policy['LengthMax']) {
            $error["ERR_LengthMax"] = $ErrorStringFormat("ERR_LengthMax", [$policy['LengthMax']]);
            $err_all[] = $error["ERR_LengthMax"];
        }
        $matches = preg_replace( '/[^0-9]/', '', $password);
        if(!empty($policy['CharDigitMin']) && strlen($matches) < $policy['CharDigitMin']) {
            $error["ERR_CharDigitMin"] = $ErrorStringFormat("ERR_CharDigitMin", [$policy['CharDigitMin']]);
            $err_all[] = $error["ERR_CharDigitMin"];
        }
        $matches = preg_replace( '/[^A-Z]/', '', $password);
        if(!empty($policy['CharUpperMin']) && strlen($matches) < $policy['CharUpperMin']) {
            $error["ERR_CharUpperMin"] = $ErrorStringFormat("ERR_CharUpperMin", [$policy['CharUpperMin']]);
            $err_all[] = $error["ERR_CharUpperMin"];
        }
        $matches = preg_replace( '/[^a-z]/', '', $password);
        if(!empty($policy['CharLowerMin']) && strlen($matches) < $policy['CharLowerMin']) {
            $error["ERR_CharLowerMin"] = $ErrorStringFormat("ERR_CharLowerMin", [$policy['CharLowerMin']]);
            $err_all[] = $error["ERR_CharLowerMin"];
        }
        if(!empty($policy['CharSpecialMin']) && !empty($policy['CharSpecial'])) {
            $matches = preg_replace( '/[^'.preg_quote($policy['CharSpecial']).']/', '', $password);
            if(strlen($matches) < $policy['CharSpecialMin']) {
                $error["ERR_CharSpecial"] = $ErrorStringFormat("ERR_CharSpecial", [$policy['CharSpecialMin'],$policy['CharSpecial']]);
                $err_all[] = $error["ERR_CharSpecial"];
            }
        }
        
        if(!empty($policy['MustContain'])) {
            foreach($policy['MustContain'] as $phrase) {
                if(strpos($password, $phrase) === false) {
                    $error["ERR_MustContain"][] = $ErrorStringFormat("ERR_MustContain", [$phrase]);
                }
            }
            if(!empty($error["ERR_MustContain"])) {
                array_push($err_all, ...$error["ERR_MustContain"]);
            }
        }
        if(!empty($policy['MustNotContain'])) {
            foreach($policy['MustNotContain'] as $phrase) {
                if(strpos($password, $phrase) !== false) {
                    $error["ERR_MustNotContain"][] = $ErrorStringFormat("ERR_MustNotContain", [$phrase]);
                }
            }
            if(!empty($error["ERR_MustNotContain"])) {
                array_push($err_all, ...$error["ERR_MustNotContain"]);
            }
        }
        if(!empty($policy['CustomValidate']) && is_callable($policy['CustomValidate'])) {
            $pass = $policy['CustomValidate']($password);
            if(!empty($pass)) {
                //error = is string or array
                $error["ERR_CustomValidate"] = $pass;
                if(is_array($pass)) {
                    array_push($err_all, ...$pass);
                } else {
                    $err_all[] = $error["ERR_CustomValidate"];
                }
                
            }
        }

        if(!empty($err_all)) {
            $error['ERR_All'] = $err_all;
        }
        return $error;
    }
}