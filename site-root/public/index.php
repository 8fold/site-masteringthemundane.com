<?php
$relativeToRoot = './';

require_once($relativeToRoot . 'constants.php');

$pageTitle = "Mastering the Mundane";

$description = 'Josh Bruce helps individuals make peace with time, space, and money. Whether itʼs physical, digital, or downsizing, Josh can probably help. Josh Bruce has been working as a coach for over a decade and is a self-proclaimed productivity super-freak.';

$poster = $url . '/media/poster.png';

require_once($relativeToRoot . 'head.php');
require_once($relativeToRoot . 'header.php');
?>
<article is="home">
	<p is="sr-only">Assisting you in making peace with: <a href="#time">time</a>, <a href="#space">space</a>, and <a href="#money">money</a>.</p>
	<section>
		<h2 id="time">Time</h2>
		<p>Create or upgrade your time management system.</p>
		<p><a href="/booking/">Schedule a session</a></p>
		<p><a href="/books/time-mastering-the-mundane/">The book</a></p>
	</section>
	<section>
		<h2 id="space">Space</h2>
		<p>Make your physical and digital spaces work <em>with</em> you.</p>
		<p><a href="/booking/">Schedule a session</a></p>
	</section>
	<section>
		<h2 id="money">Money</h2>
		<p>Get a grip on your finances and relationship with money.</p>
		<p><a href="/booking/">Schedule a session</a></p>
	</section>
</article>
<?php require_once($relativeToRoot . 'foot.php'); ?>
