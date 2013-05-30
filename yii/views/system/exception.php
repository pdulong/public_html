<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<title><?php echo CHtml::encode($data['type']); ?></title>
	<style type="text/css">
		/*<![CDATA[*/
		body {font-family:"Verdana";font-weight:normal;color:black;background-color:white;}
		h1 { font-family:"Verdana";font-weight:normal;font-size:18pt;color:red;background:#fee; }
		h3 { font-family:"Verdana";font-weight:bold;font-size:11pt;background:#eee;}
		h1,h3 {padding:5px 2px;}
		p { font-family:"Verdana";font-size:9pt;}
		pre {font-family:"Lucida Console";font-size:10pt;}
		.version {color: gray;font-size:8pt;border-top:1px solid #aaa;}
		.message {color: maroon;}
		.source {font-family:"Lucida Console";font-weight:normal;background-color:#ffc;}
		.error {background-color:#fee;display:block;color:red;}
		.traceline{display:block;}
		.traceline:hover {background: #ddd;}
		.args { color: #678797; }
		/*]]>*/
	</style>
</head>

<body>
<h1><?php echo $data['type']; ?></h1>

<h3>Description</h3>
<p class="message"><?php echo nl2br(CHtml::encode($data['message'])); ?></p>

<h3>Source</h3>
<p><?php echo CHtml::encode($data['file'].'('.$data['line'].')'); ?></p>
<div class="source">
<pre>
<?php
if (empty($data['source']))
	echo 'Geen broncode beschikbaar.';
else
{
	foreach ($data['source'] as $line=>$code)
	{
		$err=CHtml::encode(sprintf("%04d: %s", $line, str_replace("\t", '    ', $code)));
		if ($line !== $data['line'])
			echo $err;
		else
			echo "<span class=\"error\">$err</span>";
	}
}
?>
</pre>
</div>

<h3>Stack Trace</h3>
<div class="callstack">
<pre class="trace"><?php echo CHtml::encode($data['trace']); ?></pre>
</div>

<div class="version">
<?php echo date('Y-m-d H:i:s', $data['time']) . ' ' . $data['version']; ?>
</div>
</body>
</html>