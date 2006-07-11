<?
function TattertoolsBirthday_TattertoolsBirthday($target) {
	$dDay = intval((gmmktime(0, 0, 0, 3, 1) - time()) / 86400);
	if ($dDay < 0)
		$dDay = intval((gmmktime(0, 0, 0, 3, 1, gmdate('Y') + 1) - time()) / 86400);
	if ($dDay == 0)
		$message = '<span style="color:blue">탄생을 축하합니다!</span>';
	else
		$message = "앞으로 {$dDay}일 남음";
	ob_start();
?>
      <div class="listbox">
        <h3>태터툴즈 생일</h3>
        <div style="text-align:center">3월 1일: <?=$message?></div>
      </div>
<?
	$target = ob_get_contents();
	ob_end_clean();
	return $target;
}
?>