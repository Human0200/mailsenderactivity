<?php
use Bitrix\Main\Mail\Mail;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
  die();

class CBPMailSenderActivity extends CBPActivity
{
  public function __construct($name)
  {
    parent::__construct($name);
    $this->arProperties = [
      "Title" => "",
      "Recipient" => "", // Здесь будет user_id, например, user_69
      "Subject" => "",
      "Message" => ""
    ];
  }

  public function Execute()
  {
    if (!\Bitrix\Main\Loader::includeModule('main')) {
      throw new Exception("Модуль 'main' не установлен.");
    }

    // Получаем ID пользователя из Recipient
    $recipientUserId = $this->Recipient;
    $this->WriteToTrackingService("Получен user_id: " . $recipientUserId);
    if (!$recipientUserId) {
      $this->WriteToTrackingService("Все полученные параметры: " . print_r($this->arProperties, true));
      throw new Exception("Получен пустой user_id.");
    }
    
    // Извлекаем email пользователя по user_id
    $userEmail = $this->getUserEmailById($recipientUserId);
    if (!$userEmail) {
      $this->WriteToTrackingService("Не удалось найти email для user_id: " . $recipientUserId);
      throw new Exception("Не удалось найти email для user_id: " . $recipientUserId);
    }

    $this->WriteToTrackingService("Найден email пользователя: " . $userEmail);

    // Формируем письмо
    $mailSubject = CBPHelper::stringify($this->Subject);
    $message = $this->Message;

    $mailFields = [
		'CHARSET' => 'utf-8',
	  'CONTENT_TYPE' => 'html',
      "TO" => $userEmail,
      "SUBJECT" => $mailSubject,
      "BODY" => $message,
      "HEADER" => []
    ];

    $this->WriteToTrackingService("Отправка письма на email: " . $userEmail);

    $result = Mail::send($mailFields);

    if (!$result) {
      $this->WriteToTrackingService("Ошибка при отправке письма.");
      throw new Exception("Ошибка при отправке письма.");
    }

    $this->WriteToTrackingService("Письмо успешно отправлено на: " . $userEmail);

    return CBPActivityExecutionStatus::Closed;
  }

  private function getUserEmailById($userId)
  {
    // Извлекаем ID без префикса (user_69 -> 69)
    $userId = str_replace('user_', '', $userId);

    $rsUser = CUser::GetByID($userId);
    if ($arUser = $rsUser->Fetch()) {
      return $arUser["EMAIL"];
    }

    return null;
  }

  public static function ValidateProperties($arTestProperties = [], CBPWorkflowTemplateUser $user = null)
  {
    $errors = [];
    if (empty($arTestProperties["Recipient"])) {
      $errors[] = ["code" => "NotExist", "message" => GetMessage("MAILSENDERACTIVITY_ERROR_RECIPIENT")];
    }
    return array_merge($errors, parent::ValidateProperties($arTestProperties, $user));
  }

  public static function GetPropertiesDialog($documentType, $activityName, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, $arCurrentValues, $formName = "")
  {
    $runtime = CBPRuntime::GetRuntime();
    $arMap = ["Recipient" => "recipient", "Subject" => "subject", "Message" => "message"];

    // Если значения ещё не установлены, извлекаем из текущих свойств активности
    if (!is_array($arCurrentValues)) {
      $arCurrentValues = [];
      $arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);

      if (is_array($arCurrentActivity["Properties"])) {
        foreach ($arMap as $propertyKey => $fieldName) {
          $arCurrentValues[$fieldName] = $arCurrentActivity["Properties"][$propertyKey] ?? "";
        }
      }
    }

    return $runtime->ExecuteResourceFile(__FILE__, "properties_dialog.php", ["arCurrentValues" => $arCurrentValues]);
  }

  public static function GetPropertiesDialogValues($documentType, $activityName, &$arWorkflowTemplate, &$arWorkflowParameters, &$arWorkflowVariables, $arCurrentValues, &$arErrors)
  {
    $arErrors = [];
    $arMap = [
      'Recipient' => 'recipient',
      'Subject' => 'subject',
      'Message' => 'message',
    ];
    $arProperties = [];

    foreach ($arMap as $key => $value) {
      $arProperties[$key] = $arCurrentValues[$value] ?? ""; // Устанавливаем значение по умолчанию, если поле пустое
    }

    $arErrors = self::ValidateProperties($arProperties);
    if (!empty($arErrors)) {
      return false;
    }

    $arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
    $arCurrentActivity["Properties"] = $arProperties;

    return true;
  }


  public function ReInitialize()
  {
    $this->Recipient = "";
    $this->Subject = "";
    $this->Message = "";
  }
}
