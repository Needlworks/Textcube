<?
function TattertoolsBirthday_TattertoolsBirthday($target) {
	global $configVal;
	$month = 3;
	$day = 1;
	if( !empty( $configVal ) ){
		$list = explode(":" , $configVal);
		if( 2 == count($list) ){
			if( is_numeric( $list[0] ) ) $month = $list[0];
			if( is_numeric( $list[1] ) ) $day = $list[1];
		}
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
        <div style="text-align:center"><?=$month?>월 <?=$day?>일: <?=$message?></div>
      </div>
<?
	$target = ob_get_contents();
	ob_end_clean();
	return $target;
}

function TattertoolsBirthdaySetting($setVal, $postCheck){
	/*month:day*/
	global $configPost;
	$month = 3;
	$day = 1;
	if( !empty( $setVal ) ){
		$list = explode(":" , $setVal);
		if( 2 == count($list) ){
			if( is_numeric( $list[0] ) ) $month = $list[0];
			if( is_numeric( $list[1] ) ) $day = $list[1];
		}
	}
	ob_start();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<style type="text/css">
	body{
		background-color:#00CCFF;
		color:#FF0000;
	}
</style>
<script type="text/javascript">
	window.resizeTo(350,200);
</script>
<title>생일을 설정해주세요</title>
<?	if( $postCheck ){?>
<script type="text/javascript">
	alert("변경되었습니다.");
</script>
<?	}?>
</head>
<body>
	<form method="post" action="<?=$configPost?>">
		내 생일은 <input type="text" name="month" size="2" value="<?=$month?>" /> 월 
		<input  type="text" name="day" size="2" value="<?=$day?>" />일 입니다.<br />
		<div width="100%" align="center">		
			<input type="submit" value="저장" />
		</div>		
	</form>
	<br />
	<div width="100%" align="center">
		<input type="button" value="닫기" onClick="self.close();" >
	</div>
</body>
</html>
<?
	$return = ob_get_contents();
	ob_end_clean();
	return $return;
}

function TaatertoolsBirthdayDataSet($post){
	if( empty( $post['month'] ) || empty( $post['day'] ) )
		return false;
	return $post['month'].':'. $post['day'] ;
}
?>