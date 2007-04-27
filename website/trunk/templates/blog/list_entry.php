<h6 class="blog_entry_header">
    Posted at <?php echo date( 'r', $svn->date ); ?> by <?php echo $svn->author; ?> at ocone.org.
</h6>
<?php
    echo $content;
?>
<h6 class="blog_entry_footer">
    <?php echo count( $comments ); ?> comments
</h6>

<hr />
