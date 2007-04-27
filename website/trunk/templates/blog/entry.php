<?php
    echo $content;
?>
<hr />
<h3>Comment on blog entry</h3>

<form action="<?php echo $baseUrl; ?>postComment" method="post">
<fieldset>
    <legend>New comment</legend>

    <label>
        Name:
        <input type="text" name="name" />
    </label>
    <label>
        Comment:
        <textarea name="comment"></textarea>
    </label>

    <label>
        Post comment
        <input type="submit" value="Post comment" />
    </label>

</fieldset>
</form>

<?php
    if ( count( $comments ) )
    {
?>
<hr />
<h3>Comments</h3>
<ul class="comments">
<?php
    foreach ( $comments as $comment )
    {
        printf( "\t<li><h3>%s at %s</h3><p>%s</p></li>\n",
            $comment['author'],
            date( 'r', $comment['date'] ),
            nl2br( htmlentities( $comment['content'] ) )
        );
    }
?>
</ul>
<?php
    }
?>
