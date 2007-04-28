<?xml version="1.0"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//DE"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de">
<head>
    <title><?php echo htmlentities( $site['title'] ); ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="Author" content="<?php echo htmlentities( $site['author'] ); ?>" />
	<link rel="Stylesheet" type="text/css" href="/style/ocone.css" media="screen" />
	<link rel="icon" href="/images/favicon.png" type="image/png" />
    <link rel="alternate" type="application/rss+xml" title="<?php echo htmlentities( $site['title'] ); ?>" href="<?php echo htmlentities( $feedUrl ); ?>" />
</head>
<body>
	<a href="/">
		<h1>oCone - Open Content Network</h1>
		<img src="/images/logo.png" alt="oCone - Open Content Network" width="202" height="66" id="logo" title="oCone - Open Content Network" />
	</a>

	<ul id="navigation">
<?php
    foreach ( $navigation as $name => $item )
    {
        if ( isset( $item['link'] ) )
        {
            printf( "<li><a %s href=\"%s\">%s</a></li>\n",
                ( $item['link'] === $url ? 'class="selected"' : '' ),
                $item['link'],
                $name
            );
        }
    }
?>
	</ul>

	<div id="main">
		<div id="content">
<?php
    echo $content;
?>
		</div>

		<div id="misc">
			<ul id="subnavigation">
<?php
    function recurseNavigation( $navigation, $highlight = null, $maxLevel = -1 )
    {
        if ( $maxLevel == 0 )
        {
            return false;
        }

        foreach ( $navigation as $name => $item )
        {
            echo "<li>";
            if ( isset( $item['link'] ) )
            {
                printf( "<a %s href=\"%s\">%s</a>",
                ( $item['link'] === $highlight ? 'class="selected"' : '' ),
                    $item['link'],
                    $name
                );
            }
            else
            {
                echo $name;
            }

            // Recurse
            if ( isset( $item['childs'] ) && count( $item['childs'] ) )
            {
                echo "\n<ul>\n";
                recurseNavigation( $item['childs'], $highlight, $maxLevel - 1 );
                echo "\n</ul>\n";
            }

            echo "</li>";
        }
    }

    recurseNavigation( $navigation, $url );
?>
			</ul>

			<div class="download">
				<h3>Download</h3>
				<a href="">
					JoCone 0.1-beta
				</a>
			</div>
		</div>

		<h6>
			Last updated to revision <?php echo $svn->revision; ?> at <?php echo date( 'r', $svn->date ); ?> by <?php echo $svn->author; ?> at ocone.org.
		</h6>
	</div>
</body>
</html>
