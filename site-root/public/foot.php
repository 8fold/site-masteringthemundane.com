<?php

$uri = $_SERVER['REQUEST_URI'];

$navigation = [
    '/' => 'Home',
    '/books/' => 'Books',
    '/coaching-and-mentoring/' => 'Coaching and Mentoring',
    '/services/' => 'Services'
];

?>
    <nav>
        <ul>
            <?php foreach ($navigation as $href => $linkText) {
                $class = '';
                if ($uri === $href) {
                    $class = ' class="current" ';
                }
                print('<li><a '. $class . 'href="' . $href . '">' . $linkText . '</a></li>');
            } ?>
        </ul>
    </nav>
	<footer>
		<p>Copyright © 2023–<?php print(date('Y')); ?> Mastering the Mundane.</p>
	</footer>
</body>
</html>
