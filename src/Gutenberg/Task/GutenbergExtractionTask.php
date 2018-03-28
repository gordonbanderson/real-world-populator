<?php

namespace Suilven\RealWorldPopulator\Gutenberg\Task;

use SilverStripe\Blog\Model\Blog;
use SilverStripe\Control\Director;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\BuildTask;
use SilverStripe\i18n\i18n;
use SilverStripe\Security\Permission;
use Suilven\RealWorldPopulator\Gutenberg\Controller\GutenbergBookExtractBlogPost;

/**
 * Defines and refreshes the elastic search index.
 */
class GutenbergExtractionTask extends BuildTask
{

    protected $title = 'Gutenberg Blog';

    protected $description = 'Download gutenberg books and convert the text into blog posts';

    private static $segment = 'gutenberg';

    protected $enabled = true;


    public function run($request)
    {

        // need a book and a title slug
        $bookURL = $_GET['book'];
        $title = $_GET['title'];

        // optionally a blog name
        $blogName = isset($_GET['blog']) ? $_GET['blog'] : 'Gutenberg';

        $blogName = urlencode($blogName);


        $canAccess = (Director::isDev() || Director::is_cli() || Permission::check("ADMIN"));
        if (!$canAccess) {
            return Security::permissionFailure($this);
        }

        $startTime = microtime(true);
        $message = function ($content) {
            print(Director::is_cli() ? "$content\n" : "<p>$content</p>");
        };

        $blog = Blog::get()->where(['Title' => $blogName])->first();

        if ($blog) {
            error_log('Reusing blog');
        } else {
            error_log('**** Creating new blog');
            $blog = new Blog();
            $blog->Title = $blogName;
            $blog->write();
            $blog->publish("Stage", "Live");
        }



        $locale = i18n::get_locale();

        $slug = strtolower($title);
        $slug = str_replace(' ', '-', $slug);
        $filename = "/tmp/{$slug}.txt";
        $url = $bookURL;
        echo $filename . "\n";
        if (!file_exists($filename)) {
            echo '+++++ downloading +++++';
            $this->download_remote_file_with_curl($url, $filename);
        }

        $maxPages = $this->config()->get('max_pages');
        $maxParas = $this->config()->get('max_paragraphs');

        error_log('MAX PAGES: ' . $maxPages);
        error_log('MAX PARAS: ' . $maxParas);



        $parsing = false;
        $handle = fopen($filename, "r");
        $paras = array();
        $para = '';
        $ctr = 1;
        $nPosts = 0;
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                $line = trim($line);

                // ignore cruft at the start
                if ($this->contains('START OF THIS PROJECT GUTENBERG', $line) ||
                    $this->contains('START OF THE PROJECT GUTENBERG', $line)) {
                    echo "\t PARSING\n";
                    $parsing = true;
                    continue;
                } elseif ($this->contains('END OF THIS PROJECT GUTENBERG', $line) ||
                    $this->contains('END OF THE PROJECT GUTENBERG', $line)) {
                    echo "\t STOP PARSING\n";
                    $parsing = false;
                    continue;
                }

                if ($parsing) {
                    if (strlen($line) === 0) {
                        if (strlen($para) > 0) {
                            $para = '<p>' . $para . '</p>';
                            array_push($paras, $para);
                            $para = '';


                            if (mt_rand(1, $maxParas) === 1 && (sizeof($paras) >= 2)) {
                                $text = implode("\n", $paras);
                                $extractTitle = array_shift($paras);
                                //Attempt to grad just the first sentence after removing para tags
                                $extractTitle = str_replace('[', '', $extractTitle);
                                $extractTitle = str_replace(']', '', $extractTitle);
                                $extractTitle = str_replace('<p>', '', $extractTitle);
                                $extractTitle = str_replace('</p>', '', $extractTitle);
                                $extractTitle = str_replace('<br/>>', '', $extractTitle);
                                $extractTitle = trim($extractTitle, '');
                                $extractTitle = $this->limit_words($extractTitle, 10); // minimize site of title


                                $splits = explode('. ', $extractTitle);
                                $extractTitle = ucwords($splits[0]);
                                $post = null;
                                $post = new GutenbergBookExtractBlogPost();
                                $post->ParentID = $blog->ID;
                                $post->Source = $title;
                                $post->Title = $extractTitle;
                                $ctr++;
                                $post->Content = trim($text);
                                $past = time() - mt_rand(0, 3600 * 24 * 730);
                                $date = date('Y-m-d', $past);

                                $post->PublishDate = $date;
                                $post->Created = $date;
                                $post->LastEdited = $date;
                                $post->Locale = $locale;
                                $post->write();

                                $post->publish("Stage", "Live");

                                $paras = array();

                                $nPosts++;
                                if ($nPosts >= $maxPages) {
                                    $parsing = false;
                                }
                            }
                        }
                    } else {
                        $para = $para . "\n" . $line;
                        echo '.';
                    }
                }
            }

            fclose($handle);

            $message("/Importing " . $title . "\n\n\n\n");
        } else {
            // error opening the file.
        }
    }


    /**
     * @param $needle
     * @param $haystack
     * @return bool true if needle is in haystack
     */
    private function contains($needle, $haystack)
    {
        return strpos($haystack, $needle) !== false;
    }


    function limit_words($string, $word_limit)
    {
        $words = explode(" ", $string);
        return implode(" ", array_splice($words, 0, $word_limit));
    }


    /**
     * Create a random date for posting purposes
     * @param $start_date
     * @param $end_date
     * @return false|string
     */
    private function randomDate($start_date, $end_date)
    {
        // Convert to timestamps
        $min = strtotime($start_date);
        $max = strtotime($end_date);

        // Generate random number using above bounds
        $val = rand($min, $max);

        // Convert back to desired date format
        return date('Y-m-d H:i:s', $val);
    }


    /**
     * @param $file_url the url of the book
     * @param $save_to where to save it
     */
    private function download_remote_file_with_curl($file_url, $save_to)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch, CURLOPT_URL, $file_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $file_content = curl_exec($ch);
        curl_close($ch);

        $downloaded_file = fopen($save_to, 'w');
        fwrite($downloaded_file, $file_content);
        fclose($downloaded_file);
    }
}
