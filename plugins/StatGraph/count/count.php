<?php

define('ROOT', '../../..');

require ROOT . '/library/include.icon.php';

foreach (new DirectoryIterator(ROOT.'/framework/boot') as $fileInfo) {
	if($fileInfo->isFile()) require_once($fileInfo->getPathname());
}
$context = Model_Context::getInstance();
$config  = Model_Config::getInstance();

if(!is_null($context->getProperty('database.database'))) {
	$context->useNamespace('database');
	$db['database'] = $context->getProperty('database');
	$db['server']   = $context->getProperty('server');
	$db['port']     = $context->getProperty('port');
	$db['username'] = $context->getProperty('username');
	$db['password'] = $context->getProperty('password');
	$context->useNamespace();
	if(POD::bind($db) === false) {
		Respond::MessagePage('Problem with connecting database.<br /><br />Please re-visit later.');
		exit;
	}
}
$database['utf8'] = (POD::charset() == 'utf8') ? true : false;

include ("src/jpgraph.php");
include ("src/jpgraph_scatter.php");
include ("src/jpgraph_line.php");

if ((isset($_REQUEST['blogid'])) && is_numeric($_REQUEST['blogid'])) {
	$blogid = intval($_REQUEST['blogid']);
}

requireComponent('Textcube.Model.Statistics');
$row = Statistics::getWeeklyStatistics();

$row = array_reverse($row);

// Y축 배열
$pos = 0;
for ($i = 7; $i >= 0; $i--) {
    $week = strtotime("-".$i." day");
    $xdata[] = date('d', $week);
	if ( !isset($row[$pos]) || (date('d', $week) != substr($row[$pos]["datemark"], -2))) {
        $ydata[] = 0;
    } else {
		$ydata[] = $row[$pos++]["visits"];
    }
}

// Create the graph. These two calls are always required
$graph = new Graph(175,120,"auto"); //그래프의 크기를 지정
$graph->img->SetAntiAliasing();
$graph->SetMargin(0,10,5,0);
$graph->SetFrame(false);
$graph->SetMarginColor('white');
$graph->SetScale("textlin");
$graph->xaxis->SetTickLabels($xdata);
$graph->xaxis->SetColor("gray7");
$graph->xaxis->title->Set(date('Y-m-d H:i:s', strtotime("now")));
$graph->xaxis->title->SetColor("gray7");
$graph->xaxis->title->SetFont(FF_FONT0);
$graph->xaxis->SetFont(FF_FONT0);
$graph->xaxis->HideTicks();
$graph->yaxis->title->Set("Hits");
$graph->yaxis->title->SetColor("gray7");
$graph->yaxis->title->SetFont(FF_FONT0);
$graph->yaxis->HideZeroLabel();
$graph->ygrid->SetFill(true,'white','#F7F7F7');
$graph->xgrid->Show();
$graph->yaxis->SetColor("white");
$graph->yaxis->SetFont(FF_FONT0);

// Create the linear plot
$lineplot = new LinePlot($ydata);
$lineplot->SetColor("gray7");
$lineplot->value->SetColor("gray5");
$lineplot->value->Show();
$lineplot->value->SetFormat("%d");
$lineplot->value->SetFont(FF_FONT0);
$lineplot->value->SetAlign("left");
$lineplot->mark->SetColor("red");
$lineplot->mark->SetWidth(1);
$lineplot->mark->SetTYPE(MARK_FILLEDCIRCLE,1);
$lineplot->SetCenter();

// Add the plot to the graph
$graph->Add($lineplot);

// Display the graph
$graph->Stroke();

?>
