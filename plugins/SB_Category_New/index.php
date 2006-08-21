<?php
function selected_search($selected, $child) {
	if($child['id'] === $selected) return true;

	foreach($child['children'] as $leaf){
		if($leaf['id'] === $selected) {
			return true;
		}
	}

	return false;
}

function _printTreeView($tree,$selected,$skin,$viewForm){
	$isSelected = ($tree['id'] === $selected)
		? 'color:#'.$skin['activeItemColor'].';background-color:#'.$skin['activeItemBgColor'].';'
		: 'color:#'.$skin['itemColor'].';background-color:#'.$skin['itemBgColor'].';';

	ob_start();
?>
<ul id="category">
	<li id="cate_total">
		<img src="<?php echo $skin['url']?>/tab_top.gif" alt="top" style="cursor:pointer" onclick="toggleCategoryAll(this);" />
		<a href="<?php echo htmlspecialchars($tree['link'])?>" style="<?php echo $isSelected?>"><?php echo htmlspecialchars($tree['label'])?> <span class="c_cnt">(<?php echo $tree['value']?>)</span></a>
<?php
	if(count($tree['children'])>0) {
?>
		<ul>
<?php
		$i = 0;
		foreach($tree['children'] as $child){
			$isSelected = ($child['id'] === $selected)
				? 'color:#'.$skin['activeItemColor'].';background-color:#'.$skin['activeItemBgColor'].';'
				: 'color:#'.$skin['itemColor'].';background-color:#'.$skin['itemBgColor'].';';
			$class = (count($tree['children']) - 1 == $i) ? 'cate_end' : 'cate';

			if(count($child['children'])>0) {
				if($viewForm || selected_search($selected, $child)) {
					$style = '';
					$img = 'tab_opened.gif';
				} else {
					$style = ' style="display:none;"';
					$img = 'tab_closed.gif';
				}
?>
			<li class="<?php echo $class?>">
				<img src="<?php echo $skin['url'].'/'.$img?>" alt="toggle" style="cursor:pointer" onclick="toggleCategory(this);" /><a href="<?php echo htmlspecialchars($child['link'])?>" style="<?php echo $isSelected?>"><?php echo htmlspecialchars($child['label'])?> <span class="c_cnt">(<?php echo $child['value']?>)</span></a>

				<ul class="sub_cate"<?php echo $style?>>
<?php
				$j = 0;
				foreach($child['children'] as $leaf){
					$treeSrc = (count($child['children']) - 1 == $j)
						? $skin['url'].'/tab_treed_end.gif'
						: $skin['url'].'/tab_treed.gif';

					$isSelected = ($leaf['id'] === $selected)
						? 'color:#'.$skin['activeItemColor'].';background-color:#'.$skin['activeItemBgColor'].';'
						: 'color:#'.$skin['itemColor'].';background-color:#'.$skin['itemBgColor'].';';
?>
					<li>
						<img src="<?php echo $treeSrc?>" alt="tree" /><a href="<?php echo htmlspecialchars($leaf['link'])?>" style="<?php echo $isSelected?>"><?php echo htmlspecialchars($leaf['label'])?> <span class="c_cnt">(<?php echo $leaf['value']?>)</span></a>
					</li>
<?php
					$j++;
				}
?>
				</ul>
<?php
			} else {
?>
			<li class="<?php echo $class?>">
				<img src="<?php echo $skin['url']?>/tab_isleaf.gif" alt="leaf" /><a href="<?php echo htmlspecialchars($child['link'])?>" style="<?php echo $isSelected?>"><?php echo htmlspecialchars($child['label'])?> <span class="c_cnt">(<?php echo $child['value']?>)</span></a>
<?php
			}
?>
			</li>
<?php
			$i++;
		}
?>
		</ul>
<?php
	}
?>
	</li>
</ul>
<?php
	$category = ob_get_contents();
	ob_end_clean();

	return $category;
}

function _getEntriesTotalCount($owner){
	global $database;
	$visibility=doesHaveOwnership()?'':'AND visibility > 0';
	return fetchQueryCell("SELECT COUNT(*) FROM {$database['prefix']}Entries WHERE owner = $owner AND draft = 0 $visibility AND category >= 0");
}

function _getCategoryNameById($owner, $id) {
	global $database;
	$result = fetchQueryCell("SELECT name FROM {$database['prefix']}Categories WHERE owner = $owner AND id = $id");
	if (is_null($result))
		return _text('전체');
	else
		return $result;
}

function _getCategoriesView($categories,$selected,$skin,$viewForm){
	global $blogURL, $owner;
	$tree = array('id' => 0, 'label' => _getCategoryNameById($owner, 0), 'value' => _getEntriesTotalCount($owner), 'link' => "$blogURL/category", 'children' => array());
	foreach ($categories as $category1) {
		$children = array();
		foreach ($category1['children'] as $category2) {
			array_push($children, array('id' => $category2['id'], 'label' => $category2['name'], 'value' => (doesHaveOwnership() ? $category2['entriesInLogin'] : $category2['entries']), 'link' => "$blogURL/category/" . encodeURL($category2['label']), 'children' => array()));
		}
		array_push($tree['children'], array('id' => $category1['id'], 'label' => $category1['name'], 'value' => (doesHaveOwnership() ? $category1['entriesInLogin'] : $category1['entries']), 'link' => "$blogURL/category/" . encodeURL($category1['label']), 'children' => $children));
	}

	return _printTreeView($tree, $selected, $skin, $viewForm);
}

function _getCategories($owner){
	global $database;
	$rows = fetchQueryAll("SELECT * FROM {$database['prefix']}Categories WHERE owner = $owner AND id > 0 ORDER BY parent, priority");
	$categories = array();
	foreach ($rows as $category) {
		if ($category['parent'] == null) {
			$category['children'] = array();
			$categories[$category['id']] = $category;
		} else if (isset($categories[$category['parent']]))
			array_push($categories[$category['parent']]['children'], $category);
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
	}

	#category .cate_end {
		background-image: url(<?php echo $skin['url']?>/navi_back_noactive_end.gif);
		background-repeat: no-repeat;
		background-position: left top;
		padding:1px 0 0 0;
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