<?php
function TextcubeBirthday_TextcubeBirthday($target) {
	$context = Model_Context::getInstance();
	$data = $context->getProperty('plugin.config');
	$month = 3;
	$day = 13;
	if( !is_null( $data ) ){
		$month = $data['month'];
		$day = $data['day'];
	}
	$dDay = intval((gmmktime(0, 0, 0, $month, $day) - time()) / 86400);
	if ($dDay < 0)
		$dDay = intval((gmmktime(0, 0, 0, $month, $day, gmdate('Y') + 1) - time()) / 86400);
	if ($dDay == 0)
		$message = '<span class="congratu">'._t("탄생을 축하합니다!").'</span>';
	else
		$message = "<span>"._f("앞으로 %1일 남음", $dDay_)."</span>";
	ob_start();
?>
      <div class="listbox">
        <h3><?php echo _t("텍스트큐브 생일");?></h3>
        <div style="text-align:center"><?php echo $month;?>월 <?php echo $day;?>일: <?php echo $message;?></div>
      </div>
<?php
	$target = ob_get_contents();
	ob_end_clean();
	return $target;
}
function TextcubeBirthdayDataSet($DATA){
	$context = Model_Context::getInstance();
	$cfg = $context->getProperty('plugin.config');
	// if( $cfg['month'] != 날짜냐?) return "잘못된 날짜입니다.";
	// 등등등등 여기서 원하는 검증을 하시고 검증 실패시 사용자에게 보여줄 에러메세지를 보내주심 됩니다.
	// 성공하면 그냥 true
	return true;
}
?>
