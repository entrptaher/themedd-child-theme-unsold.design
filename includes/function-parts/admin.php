<?php

function allow_contributor_uploads()
{
    $contributor = get_role('contributor');
    $contributor->add_cap('upload_files');
    $contributor->add_cap('edit_published_posts');
    $contributor->add_cap('edit_others_posts');
}