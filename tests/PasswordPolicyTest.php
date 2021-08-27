<?php

/*
* https://github.com/ancientec/password-policy
*
* Ancientec Co., Ltd. 
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

use PHPUnit\Framework\TestCase;
use Ancientec\PasswordPolicy\PasswordPolicy;

require __DIR__.'/../vendor/autoload.php';

class PasswordPolicyTest extends TestCase {
    function __construct() {
      parent::__construct();
      $this->policy = ["LengthMin" => 6,
      "LengthMax" => 12,
      "CharDigitMin" => 0,
      "CharUpperMin" => 0,
      "CharLowerMin" => 0,
      "CharSpecial" => "~!@#$%^&*()-=_+",
      "CharSpecialMin" => 0];
    }
    function testMultiplePolicies() {
        $testPolicy1 = array_merge($this->policy, ["LengthMin" => 5]);
        $testPolicy2 = array_merge($this->policy, ["LengthMin" => 4]);
        $passwordPolicy1 = new PasswordPolicy($testPolicy1,[],"policy1");
        $passwordPolicy2 = new PasswordPolicy($testPolicy2,[],"policy2");
        $policy1 = PasswordPolicy::getPolicy("policy1");
        $policy2 = PasswordPolicy::getPolicy("policy2");
        
        $this->assertEquals(5, $policy1["LengthMin"]);
        $this->assertEquals(4, $policy2["LengthMin"]);

        $this->assertFalse($passwordPolicy2->isValid("1234","policy1"));
        $this->assertTrue($passwordPolicy1->isValid("1234","policy2"));
    }
    function testLengthMinError() {
        $testPolicy = array_merge($this->policy, ["LengthMin" => 6]);
        $passwordPolicy = new PasswordPolicy($testPolicy);
        $result = $passwordPolicy->validate("123");
        $this->assertTrue(array_key_exists("ERR_LengthMin", $result));
    }
    function testErrorString() {
        $testPolicy = array_merge($this->policy, ["LengthMin" => 6]);
        $passwordPolicy = new PasswordPolicy($testPolicy, ["ERR_LengthMin" => "password requires at least {0} characters"], "test_group");
        $result = $passwordPolicy->validate("123", "test_group");
        $this->assertEquals("password requires at least 6 characters", $result["ERR_LengthMin"]);
    }
    function testLengthMaxError() {
        $testPolicy = array_merge($this->policy, ["LengthMax" => 12]);
        $passwordPolicy = new PasswordPolicy($testPolicy);
        $result = $passwordPolicy->validate("1234567890123");
        $this->assertTrue(array_key_exists("ERR_LengthMax", $result));
    }
    function testLengthOK() {
        $testPolicy = array_merge($this->policy, ["LengthMin" => 6, "LengthMax" => 12]);
        $passwordPolicy = new PasswordPolicy($testPolicy);
        $result = $passwordPolicy->validate("123456");
        $this->assertTrue(empty($result));
    }
    function testCharDigitMinError() {
        $testPolicy = array_merge($this->policy, ["CharDigitMin" => 2,]);
        $passwordPolicy = new PasswordPolicy($testPolicy);
        $result = $passwordPolicy->validate("abcdef1");
        $this->assertTrue(array_key_exists("ERR_CharDigitMin", $result));
    }
    function testCharDigitMinOK() {
        $testPolicy = array_merge($this->policy, ["CharDigitMin" => 2,]);
        $passwordPolicy = new PasswordPolicy($testPolicy);
        $result = $passwordPolicy->validate("abcdef123");
        $this->assertTrue(empty($result));
    }
    function testCharUpperMinError() {
        $testPolicy = array_merge($this->policy, ["CharUpperMin" => 1,]);
        $passwordPolicy = new PasswordPolicy($testPolicy);
        $result = $passwordPolicy->validate("abcdef123");
        $this->assertTrue(array_key_exists("ERR_CharUpperMin", $result));
    }
    function testCharUpperMinOK() {
        $testPolicy = array_merge($this->policy, ["CharUpperMin" => 2,]);
        $passwordPolicy = new PasswordPolicy($testPolicy);
        $result = $passwordPolicy->validate("abcdef123AB");
        $this->assertTrue(empty($result));
    }
    function testCharLowerMinError() {
        $testPolicy = array_merge($this->policy, ["CharLowerMin" => 1,]);
        $passwordPolicy = new PasswordPolicy($testPolicy);
        $result = $passwordPolicy->validate("ABCDEF123");
        $this->assertTrue(array_key_exists("ERR_CharLowerMin", $result));
    }
    function testCharLowerMinOK() {
        $testPolicy = array_merge($this->policy, ["CharLowerMin" => 2,]);
        $passwordPolicy = new PasswordPolicy($testPolicy);
        $result = $passwordPolicy->validate("abcdef123AB");
        $this->assertTrue(empty($result));
    }
    function testCharSpecialError() {
        $testPolicy = array_merge($this->policy, ["CharSpecialMin" => 1,]);
        $passwordPolicy = new PasswordPolicy($testPolicy);
        $result = $passwordPolicy->validate("ABCDEF123");
        $this->assertTrue(array_key_exists("ERR_CharSpecial", $result));
    }
    function testCharSpecialError2() {
        $testPolicy = array_merge($this->policy, ["CharSpecial" => "()", "CharSpecialMin" => 1,]);
        $passwordPolicy = new PasswordPolicy($testPolicy);
        $result = $passwordPolicy->validate("ABCDEF123!3424");
        $this->assertTrue(array_key_exists("ERR_CharSpecial", $result));
    }
    function testCharSpecialOK() {
        $testPolicy = array_merge($this->policy, [ "CharSpecialMin" => 2,]);
        $passwordPolicy = new PasswordPolicy($testPolicy);
        $result = $passwordPolicy->validate("ABCDEF!()");
        $this->assertTrue(empty($result));
    }
    function testPasswordStrongOK() {
        $testPolicy = ["LengthMin" => 8,
        "LengthMax" => 32,
        "CharDigitMin" => 1,
        "CharUpperMin" => 1,
        "CharLowerMin" => 1,
        "CharSpecial" => "~!@#$%^&*()-=_+",
        "CharSpecialMin" => 1];
        $passwordPolicy = new PasswordPolicy($testPolicy);
        $result = $passwordPolicy->validate("abcDEF123!3424");
        $this->assertTrue(empty($result));
    } 
    function testValidOK() {
        $testPolicy = ["LengthMin" => 8,
        "LengthMax" => 32,
        "CharDigitMin" => 1,
        "CharUpperMin" => 1,
        "CharLowerMin" => 1,
        "CharSpecial" => "~!@#$%^&*()-=_+",
        "CharSpecialMin" => 1];
        $passwordPolicy = new PasswordPolicy($testPolicy);
        $result = $passwordPolicy->isValid("abcDEF123!3424");
        $this->assertTrue($result);
    }
    function testCustomValidate() {
        $testPolicy = ["LengthMin" => 8,
        "LengthMax" => 32,
        "CharDigitMin" => 1,
        "CharUpperMin" => 1,
        "CharLowerMin" => 1,
        "CharSpecial" => "~!@#$%^&*()-=_+",
        "CharSpecialMin" => 1,
        "CustomValidate" => function($password) { return "ERR_CustomValidate".$password;}];
        $passwordPolicy = new PasswordPolicy($testPolicy);
        $result = $passwordPolicy->validate("abcDEF123!3424");
        $this->assertTrue(array_key_exists("ERR_CustomValidate", $result));
    }
    function testErrorStringFormat() {
        $testPolicy = array_merge($this->policy, ["LengthMin" => 6, "ErrorStringFormat" => function($error, $values){
            return "minimum length must be {$values[0]}";
        }]);
        $passwordPolicy = new PasswordPolicy($testPolicy);
        $result = $passwordPolicy->validate("123");
        $this->assertEquals("minimum length must be 6", $result["ERR_LengthMin"]);
    }
    function testErrorStringFormatCharSpecial(){
        $testPolicy = array_merge($this->policy, ["LengthMin" => 1, "CharSpecialMin" => 1, "ErrorStringFormat" => function($error, $values){
            return "must contain {$values[0]} character(s) of {$values[1]}";
        }]);
        $passwordPolicy = new PasswordPolicy($testPolicy);
        $result = $passwordPolicy->validate("123");
        $this->assertEquals("must contain 1 character(s) of {$testPolicy['CharSpecial']}", $result["ERR_CharSpecial"]);
    }
    function testMustContain(){
        $testPolicy = array_merge($this->policy, ["LengthMin" => 1,"MustContain" => ["abc", "def", "123"]]);
        $passwordPolicy = new PasswordPolicy($testPolicy);
        $result = $passwordPolicy->validate("123");
        $this->assertTrue(array_key_exists("ERR_MustContain", $result));
        $this->assertEquals(2, count($result["ERR_MustContain"]));
    }
    function testMustNotContain(){
        $testPolicy = array_merge($this->policy, ["LengthMin" => 1,"MustNotContain" => ["1", "2", "a"]]);
        $passwordPolicy = new PasswordPolicy($testPolicy);
        $result = $passwordPolicy->validate("123");
        $this->assertTrue(array_key_exists("ERR_MustNotContain", $result));
        $this->assertEquals(2, count($result["ERR_MustNotContain"]));
    }
    function testMissingPolicy(){
        //Missing defined policies
        $passwordPolicy = new PasswordPolicy($this->policy,[],"exist");
        $this->assertFalse($passwordPolicy->isValid("123abc","non_existing"));
        $result = $passwordPolicy->validate("123abc","non_existing");
        $this->assertEquals("Missing defined policies", $result["ERR_NoDefinedPolicies"]);
    }
}