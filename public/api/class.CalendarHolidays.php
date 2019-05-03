
<?php
/**
 * Gets holiday for a year, defaults to slovenian slovenian holidays
 */

class CalendarHolidays
{
    protected $langObj = null;
    protected $holidayDates = [];
    protected $fixedHolidayDatesSi = [
        '01-01' => ['text' => 'Novo leto', 'non_workday' => true],
        '01-02' => ['text' => 'Novo leto', 'non_workday' => true],
        '02-08' => ['text' => 'Prešernov dan', 'non_workday' => true],
        '04-27' => ['text' => 'Dan upora proti okupatorju', 'non_workday' => true],
        '05-01' => ['text' => 'Praznik dela', 'non_workday' => true],
        '05-02' => ['text' => 'Praznik dela', 'non_workday' => true],
        '06-08' => ['text' => 'Dan Primoža Trubarja', 'non_workday' => false],
        '06-25' => ['text' => 'Dan državnosti', 'non_workday' => true],
        '08-15' => ['text' => 'Dan MV', 'non_workday' => true],
        '08-17' => ['text' => 'Združitev prekmurskih Slovencev z matičnim narodom', 'non_workday' => false],
        '09-15' => ['text' => 'Vrnitev Primorske k matični domovini', 'non_workday' => false],
        '10-25' => ['text' => 'Dan suverenosti', 'non_workday' => false],
        '10-31' => ['text' => 'Dan reformacije', 'non_workday' => true],
        '11-01' => ['text' => 'Dan spomina na mrtve', 'non_workday' => true],
        '11-23' => ['text' => 'Dan Rudolfa Maistra', 'non_workday' => false],
        '12-25' => ['text' => 'Božič', 'non_workday' => true],
        '12-26' => ['text' => 'Dan samostojnosti in enotnosti', 'non_workday' => true],
    ];

    public function __construct($year = null, $langObj)
    {
        $this->setLangObj($langObj);
        $year = $year != null ?  $year : date("Y");
        $this->setHolidayDates($this->fixedHolidayDatesSi);
        ksort($this->holidayDates);
    }

    public function getHolidayDates()
    {
        return $this->holidayDates;
    }

    /**
     * Set fixed and Easter holiday dates
     */
    public function setHolidayDates($dates = null, $year = null)
    {
        include_once "class.Lang.php";
        $lng = new Lang($this->language);

        $dates = $dates != null ? $dates : $this->fixedHolidayDatesSi;
        $year = $year != null ?  $year : date("Y");
        
        // Set fixed holiday dates
        foreach($dates as $date => $hDates) {
            $this->holidayDates[($year . '-' . $date)] = $hDates;
        }

        // Add Easter dates (Sunday and Monday)
        $easterDateObj = new DateTime($this->getEasterDate($year));
        $easterDate = $easterDateObj->format("Y-m-d");
        $easterMondayDate = $easterDateObj->add(new DateInterval('P1D'))->format("Y-m-d");
        $this->holidayDates[$easterDate] = [$this->langObj->Get('easter_holiday_names', 0), 'non_workday' => true];
        $this->holidayDates[$easterMondayDate] = [$this->langObj->Get('easter_holiday_names', 1), 'non_workday' => true];
    }

    public function getEasterDate($year = null)
    {
        $year = $year != null ?  $year : date("Y");

        // get first spring day timestamp (21.3.) 
        $base = new DateTime("$year-03-21");
        // PHPs own function to get number of days till Easter after first spring day
        $days = easter_days($year);
        // return the date
        return $base->add(new DateInterval("P{$days}D"))->format("Y-m-d");
    }

    public function setLangObj($langObj)
    {
        $this->langObj = $langObj;
    }
}