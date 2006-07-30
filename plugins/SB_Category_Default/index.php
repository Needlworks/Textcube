<?php
function _printTreeView($tree,$selected,$skin,$xhtml=false){
	if($xhtml){
		echo '<ul>';
		$isSelected=($tree['id']===$selected)?' class="selected"':'';
		echo "<li$isSelected><a href=\"",htmlspecialchars($tree['link']),'">',htmlspecialchars($tree['label'])," <span class=\"c_cnt\">({$tree['value']})</span></a>";
		if(sizeof($tree['children'])>0)
			echo '<ul>';
		foreach($tree['children'] as $child){
			$isSelected=($child['id']===$selected)?' class="selected"':'';
			echo "<li$isSelected><a href=\"",htmlspecialchars($child['link']),'">',htmlspecialchars($child['label'])," <span class=\"c_cnt\">({$child['value']})</span></a>";
			if(sizeof($child['children'])>0)
				echo '<ul>';
			foreach($child['children'] as $leaf){
				$isSelected=($leaf['id']===$selected)?' class="selected"':'';
				echo "<li$isSelected><a href=\"",htmlspecialchars($leaf['link']),'">',htmlspecialchars($leaf['label'])," <span class=\"c_cnt\">({$leaf['value']})</span></a></li>";
			}
			if(sizeof($child['children'])>0)
				echo '</ul>';
			echo '</li>';
		}
		if(sizeof($tree['children'])>0)
			echo "</ul>";
		echo '</li></ul>';
		return ;
	}
	$action=0;?>
<script type="text/javascript">
//<![CDATA[
	var expanded = false;
	function expandTree() {
<?php
	foreach($tree['children'] as $level1){
		if(!empty($level1['children'])){?>
		expandFolder(<?php echo $level1['id']?>, true);
<?php
		}
	}?>
	}
	
	function expandFolder(category, expand) {
		var oLevel1 = document.getElementById("category_" + category);
		var oImg = oLevel1.getElementsByTagName("img")[0];
		switch (expand) {
			case true:
				oImg.src = "<?php echo $skin['url']?>/tab_opened.gif";
				showLayer("category_" + category + "_children");
				return true;
			case false:
				oImg.src = "<?php echo $skin['url']?>/tab_closed.gif";
				hideLayer("category_" + category + "_children");
				return true;
		}
		return false;
	}
	
	function toggleFolder(category) {
		var oLevel1 = document.getElementById("category_" + category);
		var oImg = oLevel1.getElementsByTagName("img")[0];
		switch (oImg.src.substr(oImg.src.length - 10, 6)) {
			case "isleaf":
				return true;
			case "closed":
				oImg.src = "<?php echo $skin['url']?>/tab_opened.gif";
				showLayer("category_" + category + "_children");
				expanded = true;
				return true;
			case "opened":
				oImg.src = "<?php echo $skin['url']?>/tab_closed.gif";
				hideLayer("category_" + category + "_children");
				expanded = false;
				return true;
		}
		return false;
	}
	var selectedNode = 0;
	function selectNode(category) {
		
		try {
			var root = document.getElementById('treeComponent');
			var prevSelectedNode= root.getAttribute('currentselectednode');			
			var oLevel = document.getElementById("category_" + selectedNode);
			var oChild = oLevel.getElementsByTagName("table")[0];
			
			oChild.style.color = "#<?php echo $skin['itemColor']?>";			
<?php
	if($skin['itemBgColor']!='')
		echo "			oChild.style.backgroundColor = \"#{$skin['itemBgColor']}\"";
	else
		echo "			oChild.style.backgroundColor = \"\"";?>			
						
			root.setAttribute('currentselectednode',category);
			document.getElementById('text_'+selectedNode).style.color="#<?php echo $skin['itemColor']?>";
			
			var oLevel = document.getElementById("category_" + category);
			var oChild = oLevel.getElementsByTagName("table")[0];
			oChild.style.color = "#<?php echo $skin['activeItemColor']?>";
<?php
	if($skin['activeItemBgColor']!='')
		echo "			oChild.style.backgroundColor = \"#{$skin['activeItemBgColor']}\"";
	else
		echo "			oChild.style.backgroundColor = \"\"";?>			
			
			document.getElementById('text_'+category).style.color="#<?php echo $skin['activeItemColor']?>";
			
			selectedNode = category;
		} catch(e) {
			alert(e.message);
		}
		
	}
	
	function setTreeStyle(skin) {
		try {
			treeNodes = document.getElementsByName("treeNode");
			for(var i=0; i<treeNodes.length; i++) {	
				if( ('category_'+selectedNode) == (treeNodes[i].getAttribute('id').value) ) {
					var oLevel = document.getElementById('category_'+i);
					var oChild = oLevel.getElementsByTagName("table")[0];
					oChild.style.color ='#'+skin['activeItemColor'];
					if (skin['activeItemBgColor'] != '' && skin['activeItemBgColor'] != undefined) {
						oChild.style.backgroundColor ='#'+skin['activeItemBgColor'];						
					} else {
						oChild.style.backgroundColor ="";						
					}
					alert(oChild.style.backgroundColor);
				} else{
					var oLevel = document.getElementById("category_" + i);
					var oChild = oLevel.getElementsByTagName("table")[0];
					oChild.style.color ='#'+skin['colorOnTree'];
					oChild.style.backgroundColor ='#'+skin['bgColorOnTree'];
					var oLevel = document.getElementById('text_'+i).style.color='#'+skin['colorOnTree'];
					alert(document.getElementById('text_'+i).style.color);
				}						
			}
		} catch(e) {
			alert(e.message);
		}
	}
//]]>
</script>
	<?php
	if($skin['itemBgColor']==""){
		$itemBgColor='';
	}else{
		$itemBgColor='background-color: #'.$skin['itemBgColor'].';';
	}?>
	<table id="treeComponent" currentselectednode="<?php echo $selected?>" cellpadding="0" cellspacing="0" style="width: 100%;"><tr>
	<td>
		<table id="category_0" name="treeNode" cellpadding="0" cellspacing="0"><tr>
			<td class="ib" style="font-size: 1px"><img src="<?php echo $skin['url']?>/tab_top.gif" width="16" onclick="expandTree()" alt=""/></td>
			<td valign="top" style="font-size:9pt; padding-left:3px">
				<table onclick="<?php
	if($action==1){?> alert(3);onclick_setimp(window, this, c_ary, t_ary); <?php
	}?>" id="imp0" cellpadding="0" cellspacing="0" style="<?php echo $itemBgColor?>"><tr>
					<?php
	if(empty($tree['link']))
		$link='onclick="selectNode(0)"';
	else
		$link='onclick="window.location.href=\''.escapeJSInAttribute($tree['link']).'\'"';?>
					<td class="branch3" <?php echo $link?>><div id="text_0" style=" color: #<?php echo $skin['itemColor']?>;"><?php echo htmlspecialchars($tree['label'])?> <?php
	if($skin['showValue'])
		print "<span class=\"c_cnt\">({$tree['value']})</span>";?></div></td>
				</tr></table>
			</td>
		</tr></table>

<?php
	$parentOfSelected=false;
	$i=count($tree['children']);
	foreach($tree['children'] as $row){
		$i--;
		if(empty($row['link']))
			$link='onclick="selectNode('.$row['id'].')"';
		else
			$link='onclick="window.location.href=\''.escapeJSInAttribute($row['link']).'\'"';?>
		<table name="treeNode"  id="category_<?php echo $row['id']?>" cellpadding="0" cellspacing="0"><tr>
			<td class="ib" style="width:39px; font-size: 1px; background-image: url('<?php echo $skin['url']?>/navi_back_noactive<?php echo ($i?'':'_end')?>.gif')"><a class="click" onclick="toggleFolder('<?php echo $row['id']?>')"><img src="<?php echo $skin['url']?>/tab_<?php echo (count($row['children'])?'closed':'isleaf')?>.gif" width="39" alt=""/></a></td>
			<td>
				<table cellpadding="0" cellspacing="0" style="<?php echo $itemBgColor?>"><tr>
					<td class="branch3" <?php echo $link?>><div id="text_<?php echo $row['id']?>" style="color: #<?php echo $skin['itemColor']?>;"><?php echo htmlspecialchars(UTF8::lessenAsEm($row['label'],$skin['labelLength']))?> <?php
		if($skin['showValue'])
			print "<span class=\"c_cnt\">({$row['value']})</span>";?></div></td>
				</tr></table>
			</td>
		</tr></table>
		<div id="category_<?php echo $row['id']?>_children" style="display:none">
<?php
		$j=count($row['children']);
		foreach($row['children'] as $irow){
			if($irow['id']==$selected)
				$parentOfSelected=$row['id'];
			$j--;
			if(empty($irow['link']))
				$link='onclick="selectNode('.$irow['id'].')"';
			else
				$link='onclick="window.location.href=\''.escapeJSInAttribute($irow['link']).'\'"';
			if(empty($irow['link']))
				$link='onclick="selectNode('.$irow['id'].')"';
			else
				$link='onclick="window.location.href=\''.escapeJSInAttribute($irow['link']).'\'"';?>
				<table id="category_<?php echo $irow['id']?>" name="treeNode" cellpadding="0" cellspacing="0"><tr>
				<td style="width:39px; font-size: 1px"><img src="<?php echo $skin['url']?>/navi_back_active<?php echo ($i?'':'_end')?>.gif" width="17" height="18" alt=""/><img src="<?php echo $skin['url']?>/tab_treed<?php
			if(!$j)
				print "_end";?>.gif" width="22" alt=""/></td>
				<td>
					<table <?php echo $link?> cellpadding="0" cellspacing="0" style="<?php echo $itemBgColor?>"><tr>
					<td class="branch3"><div id="text_<?php echo $irow['id']?>" style="color: #<?php echo $skin['itemColor']?>;"><?php echo htmlspecialchars(UTF8::lessenAsEm($irow['label'],$skin['labelLength']))?> <?php echo ($skin['showValue']?"<span class=\"c_cnt\">({$irow['value']})</span>":'')?></div></td>
					</tr></table>
				</td>
				</tr></table>
<?php
		}?>
		</div>
<?php
	}?>
	</td></tr></table>
<?php
	if(is_numeric($selected)){?>
<script type="text/javascript">
//<![CDATA[
<?php
		if($parentOfSelected){?>
	expandFolder(<?php echo $parentOfSelected?>, true);
<?php
		}?>
	selectNode(<?php echo $selected?>);
//]]>
</script>
<?php
	}
} //end printTreeView

function _getEntriesTotalCount($owner){
	global $database;
	$visibility=doesHaveOwnership()?'':'AND visibility > 0';
	return fetchQueryCell("SELECT COUNT(*) FROM {$database['prefix']}Entries WHERE owner = $owner AND draft = 0 $visibility AND category >= 0");
}

function _getCategoriesView($categories,$selected,$skin,$xhtml=false){
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
	_printTreeView($tree,$selected,$skin,$xhtml);
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

function SB_Category_Default($target) {
	global $owner;

	$target .= _getCategoriesView(_getCategories($owner),isset($category)?$category:true,_getCategoriesSkin());

	return $target;
}
?>