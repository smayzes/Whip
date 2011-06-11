<!DOCTYPE html>
<html>
<head>
	<title> Whip Template Website </title>
<!-- Style sheets -->
`% if $css %`
    `% foreach $css as $c %`<link rel="stylesheet" type="text/css" href="`$c`" />
    `% endfor %`
`% endif %`
</head>
<body>

`% include /structure/nav.tpl %`