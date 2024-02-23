<!doctype html>
<html lang="en-US">
<head>
	<!-- Character set and mobile fix -->
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,initial-scale=1">

	<!-- Favicons -->
	<link rel="apple-touch-icon" sizes="180x180" href="/favicons/apple-touch-icon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="/favicons/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="/favicons/favicon-16x16.png">
	<link rel="manifest" href="/favicons/site.webmanifest">
	<link rel="mask-icon" href="/favicons/safari-pinned-tab.svg" color="#000000">
	<link rel="shortcut icon" href="/favicons/favicon.ico">
	<meta name="msapplication-TileColor" content="#ffffff">
	<meta name="msapplication-config" content="/favicons/browserconfig.xml">
	<meta name="theme-color" content="#ffffff">

  	<!-- HTML Meta Tags -->
  	<title><?php print($pageTitle); ?></title>
  	<meta name="description" content="<?php print($description); ?>">
	<meta name="keywords" content="<?php print($keywords); ?>">

  	<!-- Facebook Meta Tags -->
  	<meta property="og:url" content="<?php print($domain); ?>">
  	<meta property="og:type" content="website">
  	<meta property="og:title" content="<?php print($pageTitle); ?>">
  	<meta property="og:description" content="<?php print($description); ?>">
  	<meta property="og:image" content="<?php print($poster); ?>">

  	<!-- Twitter Meta Tags -->
  	<meta name="twitter:card" content="summary_large_image">
  	<meta property="twitter:domain" content="<?php print($domain); ?>">
  	<meta property="twitter:url" content="<?php print($url); ?>">
  	<meta name="twitter:title" content="<?php print($pageTitle); ?>">
  	<meta name="twitter:description" content="<?php print($description); ?>">
  	<meta name="twitter:image" content="<?php print($poster); ?>">

    <!-- Styles w/ query cache break -->
    <link type="text/css" rel="stylesheet" href="/css/main.css?v=1.0.1">
</head>
<body>
