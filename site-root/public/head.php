<!doctype html>
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

	<style type="text/css">
		@charset "UTF-8";
		@font-face {
    		font-family: "hyper-reg";
    		font-weight: 400;
    		font-style: normal;
    		src: url(/fonts/Atkinson-Hyperlegible-Regular-102a.woff2);
    		font-display:block
		}git

		@font-face {
    		font-family: "hyper-bold";
    		font-weight: 700;
    		font-style: normal;
    		src: url(/fonts/Atkinson-Hyperlegible-Bold-102a.woff2);
    		font-display:block
		}

		* {
			font-family: "hyper-reg", Verdana, Geneva, sans-serif;
		}

		h1 > a {
			display: block;
			height: 50px;
			width: auto;
			background-image: url('/ui/mark.svg');
			background-repeat: no-repeat;
			background-position: center center;
		}

        article p, article ul {
            line-height: 1.5rem;
        }

		[is="sr-only"] {
			position: absolute;
  		  	left: -999em;
  			right: auto;
		}

		blockquote {
			margin-top: 2rem;
			margin-bottom: 4rem;
			text-align: center;
			font-style: italic;
			font-size: 1.5rem;
		}

		article[is="home"] > section {
			margin-top: 4rem;
			padding: 2rem;
			padding-top: 75px;
			background-repeat: no-repeat;
			background-size: 100px auto;
			background-position: top center;
		}

		article[is="home"] > section:first-of-type {
			background-image: url('/ui/icon-time.svg');
		}

		article[is="home"] > section:nth-of-type(2) {
			background-image: url('/ui/icon-space.svg');
		}

		article[is="home"] > section:last-of-type {
			background-image: url('/ui/icon-money.svg');
		}

		section > h2 {
			text-align: center;
		}

		section a,
        nav a {
            color: hsla(0, 0%, 0%, 1);
            text-decoration-color: hsla(10, 98%, 26%, 1);
            text-decoration-thickness: 0.1rem;
            text-underline-offset: 0.1rem;
			transition:
				text-decoration-thickness 0.3s;
		}

		section a:hover,
        nav a:hover {
            text-decoration-thickness: 0rem;
		}

        article[is="home"] > section a {
            display: block;
			padding: 1rem 0.2rem;
			border-radius: 5px;
            background-color: hsla(10, 98%, 26%, 1);
            color: hsla(10, 100%, 100%, 1);
            text-decoration: none;
            text-align: center;
            font-weight: bold;
			transition:
				background-color 0.3s,
				color 0.3s;
        }

        article[is="home"] > section a:hover {
			background-color: hsla(10, 74%, 90%, 1);
			color: hsla(10, 0%, 0%, 1);
        }

        figure > img {
            margin: 0 auto;
            display: block;
            border: 2px solid hsla(10, 98%, 26%, 1);
        }

        figure > figcaption {
            margin-top: 0.5rem;
            text-align: center;
            font-size: 0.75rem;
        }

		footer {
			margin-top: 4rem;
		}

		footer > p {
			text-align: center;
		}

		@media screen and (min-width: 700pt) {
			body {
				max-width: 800pt;
				margin: 0 auto;
			}

			article {
				display: grid;
				column-gap: 2rem;
				min-height: 100vh;
			}

			article[is="home"] {
				grid-template-columns: auto auto auto;
			}

			article[is="home"] > section {
				padding-top: 100px;
			}

            article[is="book-details"] {
                grid-template-columns: auto auto;
            }

            nav {
                text-align: center;
            }

            nav > ul {
                margin: 0 auto;
                padding: 0;
                display: flex;
                /* grid-template-columns: min-content; */
                /* display: inline; */
                list-style: none;
            }

            nav a {
                display: inline-block;
                padding: 1rem;
            }

            /* nav > ul > li {
                float: left;
            } */

            nav > ul > li:first-of-type:after {
                content: '|';
            }
		}
	</style>
</head>
<body>
