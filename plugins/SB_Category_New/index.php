<?php
function _printTreeView($tree,$selected,$skin,$viewForm){
	$isSelected = ($tree['id'] === $selected) ? ' class="selected"' : '';

	echo '<ul id="category">'.CRLF;
	echo TAB.'<li id="cate_total"'.$isSelected.'><img src="'.$skin['url'].'/tab_top.gif" alt="top" style="cursor:pointer" onclick="toggleCategoryAll(this);" /><a href="',htmlspecialchars($tree['link']),'">',htmlspecialchars($tree['label']),' <span class="c_cnt">('.$tree['value'].')</span></a>'.CRLF;

	if(count($tree['children'])>0) {
		echo TAB.TAB.'<ul>'.CRLF;
		
		$i = 0;
		foreach($tree['children'] as $child){
			$classes = ($child['id'] === $selected) ? 'selected ' : '';
			$classes .= (count($tree['children']) - 1 == $i) ? 'cate_end' : 'cate';

			if(count($child['children'])>0) {
				if($viewForm) {
					$style = '';
					$img = 'tab_opened.gif';
				} else {
					$style = ' style="display:none;"';
					$img = 'tab_closed.gif';
				}

				echo TAB.TAB.TAB.'<li class="'.$classes.'"><img src="'.$skin['url'].'/'.$img.'" alt="toggle" style="cursor:pointer" onclick="toggleCategory(this);" /><a href="',htmlspecialchars($child['link']),'">',htmlspecialchars($child['label']),' <span class="c_cnt">('.$child['value'].')</span></a>'.CRLF;

				echo TAB.TAB.TAB.TAB.'<ul class="sub_cate"'.$style.'>'.CRLF;

				$j = 0;
				foreach($child['children'] as $leaf){

					$treeSrc = (count($child['children']) - 1 == $j) ? $skin['url'].'/tab_treed_end.gif' : $skin['url'].'/tab_treed.gif';

					$isSelected = ($leaf['id'] === $selected) ? ' class="selected"' : '';
					echo TAB.TAB.TAB.TAB.TAB.'<li'.$isSelected.'><img src="'.$treeSrc.'" alt="tree" /><a href="',htmlspecialchars($leaf['link']),'">',htmlspecialchars($leaf['label']),' <span class="c_cnt">('.$leaf['value'].')</span></a></li>'.CRLF;

					$j++;
				}
				echo TAB.TAB.TAB.TAB.'</ul>'.CRLF;
			} else {
				echo TAB.TAB.TAB.'<li class="'.$classes.'"><img src="'.$skin['url'].'/tab_isleaf.gif" alt="leaf" /><a href="',htmlspecialchars($child['link']),'">',htmlspecialchars($child['label']),' <span class="c_cnt">('.$child['value'].')</span></a>'.CRLF;
			}
			echo TAB.TAB.TAB.'</li>'.CRLF;
			$i++;
		}
		echo TAB.TAB.'</ul>'.CRLF;
	}
	echo TAB.'</li>'.CRLF;
	echo '</ul>'.CRLF;
}

function _getEntriesTotalCount($owner){
	global $database;
	$visibility=doesHaveOwnership()?'':'AND visibility > 0';
	return fetchQueryCell("SELECT COUNT(*) FROM {$database['prefix']}Entries WHERE owner = $owner AND draft = 0 $visibility AND category >= 0");
}

function _getCategoriesView($categories,$selected,$skin,$viewForm){
	global $blogURL,$owner;
	if(doesHaveOwnership()){
		$entriesSign='entriesInLogin';
	}else{
		$entriesSign='entries';
	}
	$tree=array('id'=>0,'label'=>_t('전체'),'value'=>_getEntriesTotalCount($owner),'link'=>"$blogURL/category",'children'=>array());
	foreach($categories as $category1){
		$children=array();
		foreach($category1['children'] as $category2){
			array_push($children,array('id'=>$category2['id'],'label'=>$category2['name'],'value'=>$category2[$entriesSign],'link'=>"$blogURL/category/".encodeURL($category1['name'].'/'.$category2['name']),'children'=>array()));
		}
		array_push($tree['children'],array('id'=>$category1['id'],'label'=>$category1['name'],'value'=>$category1[$entriesSign],'link'=>"$blogURL/category/".encodeURL($category1['name']),'children'=>$children));
	}
	ob_start();
	_printTreeView($tree,$selected,$skin,$viewForm);
	$view=ob_get_contents();
	ob_end_clean();
	return $view;
}

function _getCategories($owner){
	global $database;
	$rows=fetchQueryAll("SELECT * FROM {$database['prefix']}Categories WHERE owner = $owner ORDER BY parent, priority");
	$categories=array();
	foreach($rows as $category){
		if($category['parent']==null){
			$category['children']=array();
			$categories[$category['id']]=$category;
		}elseif(isset($categories[$category['parent']]))
			array_push($categories[$category['parent']]['children'],$category);
	}
	return $categories;
}

function _getCategoriesSkin(){
	global $database;
	global $owner,$service;
	$sql="select * from {$database['prefix']}SkinSettings where owner = $owner";
	$setting=fetchQueryRow($sql);
	$skin=array('name'=>"{$setting['skin']}",'url'=>$service['path']."/image/tree/{$setting['tree']}",'labelLength'=>$setting['labelLengthOnTree'],'showValue'=>$setting['showValueOnTree'],'bgColor'=>"{$setting['bgColorOnTree']}",'itemColor'=>"{$setting['colorOnTree']}",'itemBgColor'=>"{$setting['bgColorOnTree']}",'activeItemColor'=>"{$setting['activeColorOnTree']}",'activeItemBgColor'=>"{$setting['activeBgColorOnTree']}",);
	return $skin;
}

function SB_Category_New($target) {
	global $configVal, $owner, $category;

	$data = fetchConfigVal($configVal);

	$viewForm = (is_null($data['viewForm'])) ? 0 : $data['viewForm'];
	$category = (isset($category)) ? $category : true;

	return $target._getCategoriesView(_getCategories($owner), $category, _getCategoriesSkin(), $viewForm);
}

function SB_Category_New_Head($target) {
	global $configVal;
	$data = fetchConfigVal($configVal);

	$skin = _getCategoriesSkin();

	ob_start();
?>
<style type="text/css">
	#category *	{
		margin:0;
		padding:0;
	}

	#category ul {
		list-style:none;
	}

	#category img {
		vertical-align:middle;
	}

	/* main category */
	#category .cate {
		background-image: url(<?php echo $skin['url']?>/navi_back_active.gif);
		background-repeat: repeat-y;
		background-position: left top;
		padding:1px 0 0 0;
		padding /**/:0;
	}

	#category .cate_end {
		background-image: url(<?php echo $skin['url']?>/navi_back_noactive_end.gif);
		background-repeat: no-repeat;
		background-position: left top;
		padding:1px 0 0 0;
		padding /**/:0;
	}

	/* sub category */
	#category .sub_cate li {
		padding:0 0 0 17px;
	}
</style>
<script type="text/javascript">
//<![CDATA[
	var categoryToggle = <?php echo $data['viewForm'] ? 'true' : 'false'; ?>;

	function toggleCategoryAll(obj) {
		var parent = obj.parentNode;
		var ul = parent.getElementsByTagName('ul');
		for(var i = 0; i < ul.length; i++) {
			if(ul[i].className != 'sub_cate') continue;

			ul[i].style.display = categoryToggle ? 'none' : 'block';
		}

		var img = parent.getElementsByTagName('img');
		for(var i = 0; i < img.length; i++) {
			if(img[i].alt != 'toggle') continue;

			img[i].src = categoryToggle ? '<?php echo $skin['url']?>/tab_closed.gif' : '<?php echo $skin['url']?>/tab_opened.gif';
		}

		categoryToggle = categoryToggle ? false : true;
	}

	function toggleCategory(obj) {
		var parent = obj.parentNode;
		var ul = parent.getElementsByTagName('ul');
		if(ul[0].style.display == 'none') {
			ul[0].style.display = 'block';
			obj.src = '<?php echo $skin['url']?>/tab_opened.gif';
		} else {
			ul[0].style.display = 'none';
			obj.src = '<?php echo $skin['url']?>/tab_closed.gif';
		}
	}
//]]>
</script>
<?php
	$target .= ob_get_contents();
	ob_end_clean();

	return $target;
}
?>