<tr>
  <td align="right" width="40%"><b><?= GetMessage("MAILSENDERACTIVITY_RECIPIENT") ?></b> :</td>
  <td width="60%">
    <?= CBPDocument::ShowParameterField("string", 'recipient', $arCurrentValues['recipient'], ['size' => '50']) ?>
  </td>
</tr>
<tr>
  <td align="right" width="40%"><b><?= GetMessage("MAILSENDERACTIVITY_SUBJECT") ?></b> :</td>
  <td width="60%">
    <?= CBPDocument::ShowParameterField("string", 'subject', $arCurrentValues['subject'], ['size' => '50']) ?>
  </td>
</tr>
<tr>
  <td align="right" width="40%"><b><?= GetMessage("MAILSENDERACTIVITY_MESSAGE") ?></b> :</td>
  <td width="60%">
    <?= CBPDocument::ShowParameterField("text", 'message', $arCurrentValues['message'], ['rows' => '5', 'cols' => '50']) ?>
  </td>
</tr>
