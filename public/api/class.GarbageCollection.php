<?php
/**
 * Garbage collection dates handling
 */
class GarbageCollection
{
    // garbage collection data (types, periods, known dates)
    protected $collectionData = [];

    public function __construct($data = null)
    {
        // Default garbage collection data
        if($data == null) {
            $this->setCollectionData(
                [
                    'bio'     => ['period' =>  7, 'known_date' => '2019-01-04'],
                    'plastic' => ['period' => 21, 'known_date' => '2019-03-13'],
                    'rest'    => ['period' => 21, 'known_date' => '2019-03-06'],
                ]);
        }
    }

    /**
     * Get garbage collection dates for given month for all types
     * @param int Numeric representation of month (1..12)
     * @param int Numeric representation of year (YYYY)
     * @return array Array of dates and types when collection is scheduled
     */
    public function getAllDates($month = null, $year = null)
    {
        $dates = [];
        foreach($this->collectionData as $type => $data) {
            // $tempDates = $this->getDatesForType($type, $month, $year);
            foreach($this->getDatesForType($type, $month, $year) as $eachDate) {
                $dates[$eachDate] = $type;
            }
            ksort($dates);
        }
        return $dates;
    }

    /**
     * Get garbage collection dates for given month and type
     * 
     * @param string Garbage type ('rest' | 'bio' | 'plastic' |...)
     * @param int Numeric representation of month (1..12)
     * @param int Numeric representation of year (YYYY)
     * @return array Array of dates when collection is scheduled
     */
    public function getDatesForType($type = 'rest', $month = null, $year = null)
    {
        // check if period and known date exist for the type
        if(!isset($this->collectionData[$type]['period']) || !isset($this->collectionData[$type]['known_date'])) {
            return [];
        }

        // known dates for colection of garbage for selected type
        $knownDate = $this->collectionData[$type]['known_date'];
        
        // Month and year
        $month = $month != null ? $month : date("n");
        $year = $year != null ? $year : date("Y");

        // Number of days in current month
        $currentMonthDays = cal_days_in_month(CAL_GREGORIAN, $month, $year);

        // Timestamps for the date() function
        $dateKnownTs = strtotime($this->collectionData[$type]['known_date']);
        $dateCurrentmonthStartTs = strtotime("{$year}-{$month}-01");

        // Sequentiial days in given year
        $dateKnownDayInYear = date("z", $dateKnownTs);
        $dateCurrentmonthStartDayInYear = date("z", $dateCurrentmonthStartTs);

        // Compile dates array for selected month and type
        $dates = [];
        for($day = 1; $day <= $currentMonthDays; $day++) {
            $diff = ($day + $dateCurrentmonthStartDayInYear - $dateKnownDayInYear) % $this->collectionData[$type]['period'];
            if($diff == 0) {
                $dates[] = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-' . str_pad($day + 1, 2, '0', STR_PAD_LEFT);
            }
        }

        return $dates;
    }

    /**
     * Set garbage collection data (types, dates, periods)
     */
    public function setCollectionData($data) 
    {
        $this->collectionData = $data;
    }
}