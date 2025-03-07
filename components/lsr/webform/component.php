<?php
use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;
use lsr\WebformTable;

if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();

/**
 * Bitrix vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $this
 * @global CMain $APPLICATION
 * @global CUser $USER
 */

$uiLang = 'ru';

if(isset($_REQUEST["ui_lang"])) {
    $uiLang = $_REQUEST["ui_lang"];
    $_SESSION["UI_LANG"] = $_REQUEST["ui_lang"] == 'ru' ? 'ru' : 'en';
}

if(!isset($_REQUEST["ui_lang"]) && isset($_SESSION["UI_LANG"])) {
    $uiLang = $_SESSION["UI_LANG"];
    define(LANGUAGE_ID, $_SESSION["UI_LANG"]);
}

Loc::setCurrentLang($uiLang); 
 
 
$arResult["PARAMS_HASH"] = md5(serialize($arParams).$this->GetTemplateName());

$arParams["USE_CAPTCHA"] = (($arParams["USE_CAPTCHA"] != "N" && !$USER->IsAuthorized()) ? "Y" : "N");
$arParams["EVENT_NAME"] = trim($arParams["EVENT_NAME"]);
if($arParams["EVENT_NAME"] == '')
	$arParams["EVENT_NAME"] = "FEEDBACK_FORM";
$arParams["EMAIL_TO"] = trim($arParams["EMAIL_TO"]);
if($arParams["EMAIL_TO"] == '')
	$arParams["EMAIL_TO"] = COption::GetOptionString("main", "email_from");
$arParams["OK_TEXT"] = trim($arParams["OK_TEXT"]);
if($arParams["OK_TEXT"] == '')
	$arParams["OK_TEXT"] = GetMessage("MF_OK_MESSAGE");

if($_SERVER["REQUEST_METHOD"] == "POST" && $_POST["submit"] <> '' && (!isset($_POST["PARAMS_HASH"]) || $arResult["PARAMS_HASH"] === $_POST["PARAMS_HASH"]))
{   
	$arResult["ERROR_MESSAGE"] = array();
	if(check_bitrix_sessid())
	{    // exit(var_dump( $_POST ));
		if(empty($arParams["REQUIRED_FIELDS"]) || !in_array("NONE", $arParams["REQUIRED_FIELDS"]))
		{
			if((empty($arParams["REQUIRED_FIELDS"]) || in_array("NAME", $arParams["REQUIRED_FIELDS"])) && mb_strlen($_POST["user_name"]) <= 1)
				$arResult["ERROR_MESSAGE"][] = GetMessage("MF_REQ_NAME");		
			if((empty($arParams["REQUIRED_FIELDS"]) || in_array("EMAIL", $arParams["REQUIRED_FIELDS"])) && mb_strlen($_POST["user_email"]) <= 1)
				$arResult["ERROR_MESSAGE"][] = GetMessage("MF_REQ_EMAIL");
			if((empty($arParams["REQUIRED_FIELDS"]) || in_array("PHONE", $arParams["REQUIRED_FIELDS"])) && mb_strlen($_POST["user_phone"]) <= 1)
				$arResult["ERROR_MESSAGE"][] = GetMessage("MF_REQ_PHONE");
		}
		if(mb_strlen($_POST["user_phone"]) > 1 && !check_email($_POST["user_email"]))
			$arResult["ERROR_MESSAGE"][] = GetMessage("MF_EMAIL_NOT_VALID");

        // Add phone string validation
        if(mb_strlen($_POST["user_email"]) > 1 && !preg_match('/^(\+)?[1-9]{1}[0-9() -]{3,14}$/i', $_POST["user_phone"]))
            $arResult["ERROR_MESSAGE"][] = GetMessage("MF_PHONE_NOT_VALID");
		
		// При попытке сохранить дубль почты или телефона выводить сообщение об ошибке вида «такая почта/телефон уже есть».
        $result = WebformTable::getList(['select' => ['ID'], 'filter' => ['EMAIL' => $_POST["user_email"]]])->fetch();
		if($result)
			$arResult["ERROR_MESSAGE"][] = GetMessage("MF_EMAIL_ALREADY_EXISTS");
			
		$result = WebformTable::getList(['select' => ['ID'], 'filter' => ['PHONE' => $_POST["user_phone"]]])->fetch();
		if($result)
			$arResult["ERROR_MESSAGE"][] = GetMessage("MF_PHONE_ALREADY_EXISTS");	

		if($arParams["USE_CAPTCHA"] == "Y")
		{
			$captcha_code = $_POST["captcha_sid"];
			$captcha_word = $_POST["captcha_word"];
			$cpt = new CCaptcha();
			$captchaPass = COption::GetOptionString("main", "captcha_password", "");
			if ($captcha_word <> '' && $captcha_code <> '')
			{
				if (!$cpt->CheckCodeCrypt($captcha_word, $captcha_code, $captchaPass))
					$arResult["ERROR_MESSAGE"][] = GetMessage("MF_CAPTCHA_WRONG");
			}
			else
				$arResult["ERROR_MESSAGE"][] = GetMessage("MF_CAPTHCA_EMPTY");

		}
		
		if(empty($arResult["ERROR_MESSAGE"]))
		{
			$arFields = Array(
				"AUTHOR"       => $_POST["user_name"],
				"AUTHOR_EMAIL" => $_POST["user_email"],
                "AUTHOR_PHONE" => $_POST["user_phone"],
				"EMAIL_TO" => $arParams["EMAIL_TO"],

			);
			if(!empty($arParams["EVENT_MESSAGE_ID"]))
			{
				foreach($arParams["EVENT_MESSAGE_ID"] as $v)
					if(intval($v) > 0)
						CEvent::Send($arParams["EVENT_NAME"], SITE_ID, $arFields, "N", intval($v));
			}
			else {		
				CEvent::Send($arParams["EVENT_NAME"], SITE_ID, $arFields);
			}
			
			$arResult["AUTHOR_NAME"]  = htmlspecialcharsbx($_POST["user_name"]);
			$arResult["AUTHOR_EMAIL"] = htmlspecialcharsbx($_POST["user_email"]);
			$arResult["AUTHOR_PHONE"] = htmlspecialcharsbx($_POST["user_phone"]);
			
			$result = WebformTable::add([
				"NAME"  => $arResult["AUTHOR_NAME"],
				"EMAIL" => $arResult["AUTHOR_EMAIL"],
				"PHONE" => $arResult["AUTHOR_PHONE"],
			]);

			if (empty($arResult["ERROR_MESSAGE"]) && !$result->isSuccess()) {
				$arResult["ERROR_MESSAGE"][] = GetMessage("MF_DATATBASE_ERROR");
				// exit(var_dump( $result->getErrorMessages() ));
			}
				
			$_SESSION["MF_NAME"] = htmlspecialcharsbx($_POST["user_name"]);
			$_SESSION["MF_EMAIL"] = htmlspecialcharsbx($_POST["user_email"]);
			LocalRedirect($APPLICATION->GetCurPageParam("success=".$arResult["PARAMS_HASH"], Array("success")));
		}

		/* $arResult["AUTHOR_NAME"]  = htmlspecialcharsbx($_POST["user_name"]);
		$arResult["AUTHOR_EMAIL"] = htmlspecialcharsbx($_POST["user_email"]);
        $arResult["AUTHOR_PHONE"] = htmlspecialcharsbx($_POST["user_phone"]); */
		// var_dump( $arResult ); exit('ready to write');		

	}
	else
		$arResult["ERROR_MESSAGE"][] = GetMessage("MF_SESS_EXP");
}
elseif($_REQUEST["success"] == $arResult["PARAMS_HASH"])
{
	$arResult["OK_MESSAGE"] = $arParams["OK_TEXT"];
}

if(empty($arResult["ERROR_MESSAGE"]))
{
	if($USER->IsAuthorized())
	{
		$arResult["AUTHOR_NAME"] = $USER->GetFormattedName(false);
		$arResult["AUTHOR_EMAIL"] = htmlspecialcharsbx($USER->GetEmail());
	}
	else
	{
		if($_SESSION["MF_NAME"] <> '')
			$arResult["AUTHOR_NAME"] = htmlspecialcharsbx($_SESSION["MF_NAME"]);
		if($_SESSION["MF_EMAIL"] <> '')
			$arResult["AUTHOR_EMAIL"] = htmlspecialcharsbx($_SESSION["MF_EMAIL"]);
	}
}

if($arParams["USE_CAPTCHA"] == "Y")
	$arResult["capCode"] =  htmlspecialcharsbx($APPLICATION->CaptchaGetCode());

$this->IncludeComponentTemplate();
