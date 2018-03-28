<?php
namespace Suilven\RealWorldPopulator\Gutenberg\Controller;

use SilverStripe\Blog\Model\BlogPost;

class GutenbergBookExtractBlogPost extends BlogPost {
	// This is not indexed but shown in the results
	private static $db = array('Source' => 'Varchar');

	private static $table_name = 'GutenbergBlogPost';
}


