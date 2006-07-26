<?php
function TattertoolsBirthday_TattertoolsBirthday($target) {
	global $configVal;
	$data = fetchConfigVal( $configVal);
	$month = 3;
	$day = 1;
	if( !is_null( $data ) ){
		$month = $data['fieldset1']['month'];
		$day = $data['fieldset1']['day'];
	}
	$dDay = intval((gmmktime(0, 0, 0, $month, $day) - time()) / 86400);
	if ($dDay < 0)
		$dDay = intval((gmmktime(0, 0, 0, $month, $day, gmdate('Y') + 1) - time()) / 86400);
	if ($dDay == 0)
		$message = '<span style="color:blue">탄생을 축하합니다!</span>';
	else
		$message = "앞으로 {$dDay}일 남음";
	ob_start();
?>
      <div class="listbox">
        <h3>태터툴즈 생일</h3>
        <div style="text-align:center"><?php echo $month?>월 <?php echo $day?>일: <?php echo $message?></div>
      </div>
<?php
	$target = ob_get_contents();
	ob_end_clean();
	return $target;
}
function TattertoolsBirthdayDataSet($DATA){
	$cfg = fetchConfigVal( $DATA );
	if( is_null ( $cfg ) )	 return '인수값이 안들어옴';
	// 등등등등 여기서 원하는 검증을 하시고 검증 실패시 사용자에게 보여줄 에러메세지를 보내주심 됩니다.
	return true;
}
?>