<?php

namespace Suilven\RealWorldPopulator\Calendar\Task;

use Carbon\Carbon;
use Faker\Factory;
use Html2Text\Html2Text;
use SilverStripe\Blog\Model\Blog;
use SilverStripe\Control\Director;
use SilverStripe\Dev\BuildTask;
use SilverStripe\i18n\i18n;
use SilverStripe\Security\Permission;
use Suilven\RealWorldPopulator\Gutenberg\Controller\GutenbergBookExtractBlogPost;
use TitleDK\Calendar\Events\Event;

/**
 * Defines and refreshes the elastic search index.
 */
class RandomCalendarTask extends BuildTask
{

    protected $title = 'Random Calendar CSV';

    protected $description = 'Create random calendar csv data';

    private static $segment = 'randomcalendar';

    protected $enabled = true;


    const SS_DATE_FORMAT = 'Y-m-d H:i:s';

    public function run($request)
    {
/*
        $events = Event::get();
        foreach($events as $event) {
            $event->write();
        }
*/
        // need a book and a title slug
        //       $bookURL = $_GET['book'];

        $canAccess = (Director::isDev() || Director::is_cli() || Permission::check("ADMIN"));
        if (!$canAccess) {
            return Security::permissionFailure($this);
        }

        $calendarName = $_GET['calendar'];

        $from = $_GET['from'];
        $to = $_GET['to'];
        $type = $_GET['type'];

        $startDate = Carbon::parse($from);
        $endDate = Carbon::parse($to);

        switch ($type) {
            case 'duration':
                $this->eventsByDuration($from, $to, $calendarName, $startDate, $endDate);
                break;
            default:
                break;
        }
    }


    private function eventsByDuration($from, $to, $calendarName, $startDate, $endDate)
    {
        error_log($startDate);
        error_log($endDate);
        $dayspan = $endDate->diffInDays($startDate);

        $faker = Factory::create();


        $file = fopen("output.csv","w");

        $line = ["Title","StartDateTime", "EndDateTime", "TimeFrameType", "Duration", "Details" ];
        fputcsv($file,$line);


        for ($i=0; $i < 200; $i++) {
            /** @var Carbon $startDate */
            $eventDate = $startDate->copy()->addDays(rand(1, $dayspan));
            $eventDate->addHours(rand(8,21));
            $eventDate->addMinutes(4*rand(1,4));
            $startDateString = $eventDate->format(self::SS_DATE_FORMAT);
            $durationInMinutes = 15*rand(4,32);
            $eventDate->addMinutes($durationInMinutes);
            $endDateString = $eventDate->format(self::SS_DATE_FORMAT);

            $duration =  date('H:i', mktime(0,$durationInMinutes));

            $line = [];
            $line[] = "(GO) " . $faker->company .' Meeting';
            $line[] = $startDateString;
            $line[] = $endDateString;
            $line[] = "Duration";
            $line[] = $duration;
            $line[] = implode("\n\n", $faker->paragraphs());

            fputcsv($file,$line);
        }

        fclose($file);

    }


    /**
     * Convert a multi-dimensional, associative array to CSV data
     * @param  array $data the array of data
     * @return string       CSV text
     */
    private function str_putcsv($data) {
        # Generate CSV data from array
        $fh = fopen('php://temp', 'rw'); # don't create a file, attempt
        # to use memory instead

        # write out the headers
        fputcsv($fh, array_keys(current($data)));

        # write out the data
        foreach ( $data as $row ) {
            fputcsv($fh, $row);
        }
        rewind($fh);
        $csv = stream_get_contents($fh);
        fclose($fh);

        return $csv;
    }
}
