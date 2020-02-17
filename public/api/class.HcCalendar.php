<?php

if(!defined('NL')) {
    define('NL', "\n");
}

class HcCalendar {  

    protected $dayLabels = array('Mon','Tue','Wed','Thu','Fri','Sat','Sun');
    protected $monthLabels = array('January','February','March','April','May','June','July', 'August', 'September', 'October', 'November', 'December');
    protected $currentYear = 0;
    protected $currentMonth = 0;
    protected $currentDay = 0;
    protected $currentDate = null;
    protected $daysInMonth = 0;
    protected $naviHref = null;
    protected $events = [];
    protected $eventIconsPath = '/img/event-icons/dark/';

    public function __construct(){     
        $this->naviHref = htmlentities($_SERVER['PHP_SELF']);
    }
        
    /**
    * Print out the calendar
    */
    public function show() 
    {
        $year  = null;
        $month = null;
         
        if(null == $year && isset($_GET['year'])) {
            $year = $_GET['year'];
        } else if(null == $year) {
            $year = date("Y", time());  
        }          
         
        if(null == $month && isset($_GET['month'])) {
            $month = $_GET['month'];
        } else if(null == $month) {
            $month = date("m",time());         
        }                  

        $this->currentYear = $year;
        $this->currentMonth = $month;
        $this->daysInMonth = $this->daysInMonth($month,$year);  
         
        $content = 
            '<div id="hc-calendar">' . NL .
            '<table>' . NL . 
            '<thead>' . NL .
            $this->createNavi() . NL .
            $this->createLabels() .
            '</thead>' . NL .
            '<tbody>' . NL;    
                                 
        $weeksInMonth = $this->weeksInMonth($month,$year);
        // Create weeks in a month
        for($i = 0; $i < $weeksInMonth; $i++) {
            $content .= '<tr>' . NL;
            //Create days in a week
            for($j = 1; $j <= 7; $j++) {
                $content .= $this->showDay($i * 7 + $j) . NL;
            }
            $content .= '</tr>' . NL;
        }

        $content .= 
            '</tbody>' . NL . 
            '</table>' . NL . 
            '</div>';

        return $content;
    }
     
    /**
    * Create the td element for table
    */
    private function showDay($cellNumber)
    {
        $today = date('Y-m-d');
        $iconClass = '';
        
        if($this->currentDay == 0) {
            $firstDayOfTheWeek = date('N', strtotime($this->currentYear . '-' . $this->currentMonth . '-01'));
            if(intval($cellNumber) == intval($firstDayOfTheWeek)) {
                $this->currentDay = 1;
            }
        }
         
        if(($this->currentDay != 0) && ($this->currentDay <= $this->daysInMonth)) {
            $this->currentDate = date('Y-m-d', strtotime($this->currentYear . '-' . $this->currentMonth . '-' . ($this->currentDay)));
            $cellContent = $this->currentDay;

            if(isset($this->events[$this->currentDate])) {

                foreach($this->events[$this->currentDate] as $key => $ev) {
                    
                    if(array_key_exists('event_icon', $ev)) {
                        $iconClass = rtrim($ev['event_icon'], '.svg');
                    } else {
                        $iconClass = '';
                    }
                    
                    if(array_key_exists('event_text', $ev)) {
                        $cellContent .= '<br>' . $ev['event_text'];
                    }
                }
            }

            $this->currentDay++;
        } else {
            $this->currentDate = null;
            $cellContent = null;
        }

        return '<td' . 
            // ID attribute for only for current month days
            ($this->currentDate != null ? ' id="d-' . $this->currentDate . '"' : '') . 
            ' class="' .
            ($this->currentDate == $today ? 'today ' : '') .
            // Saturdays and Sundays
            ((intval($cellNumber) % 7 == 6 || intval($cellNumber) % 7 == 0) ? 'non-work-day' : 'work-day') . " $iconClass" . '">' .
            $cellContent . '</td>';
    }
     
    /**
    * create navigation
    */
    private function createNavi() 
    {
        $nextMonth = $this->currentMonth == 12 ? 1 : intval($this->currentMonth) + 1;
        $nextYear = $this->currentMonth == 12 ? intval($this->currentYear) + 1 : $this->currentYear;
        $preMonth = $this->currentMonth == 1 ? 12 : intval($this->currentMonth) - 1;
        $preYear = $this->currentMonth == 1 ? intval($this->currentYear) - 1 : $this->currentYear;

        // $currentMonthName = $this->monthLabels[$this->currentMonth];
        return '<tr>' . NL . 
        '<td colspan="2">' . NL .
        // Navigation currently disabled
        // '<a class="prev" href="' . $this->naviHref . '?month=' . sprintf('%02d',$preMonth) . '&year=' . $preYear . '">Prev</a>' . NL .
        '</td>' . NL . 
        '<td colspan="3">' .
        // '<span class="title">' . date('Y | M', strtotime($this->currentYear . '-' . $this->currentMonth . '-1')) . '</span>' . NL .
        '<span class="title">' . $this->monthLabels[intval($this->currentMonth) - 1] . ' ' . $this->currentYear . '</span>' . NL .
        '</td>' . NL .
        '<td colspan="2">' . NL .
        // Navigation currently disabled
        // '<a class="next" href="' . $this->naviHref . '?month=' . sprintf("%02d", $nextMonth) . '&year=' . $nextYear . '">Next</a>' . NL .
        '</td>' . NL . '</tr>';
    }

    /**
    * create calendar day labels
    */
    private function createLabels()
    {
        $content='<tr>' . NL;
        foreach($this->dayLabels as $index => $label) {
            $content .= '<th class="title">' . $label . '</th>' . NL;
        }
        $content .= '<tr>' . NL;
        return $content;
    }
     
    /**
    * calculate number of weeks in a particular month
    */
    private function weeksInMonth($month = null, $year = null) 
    {
        if( null == ($year)) {
            $year =  date("Y", time()); 
        }
         
        if(null == ($month)) {
            $month = date("m", time());
        }
         
        // find number of days in this month
        $daysInMonths = $this->daysInMonth($month, $year);
        $numOfweeks = ($daysInMonths % 7 == 0 ? 0 : 1) + intval($daysInMonths / 7);
        $monthEndingDay = date('N', strtotime($year . '-' . $month . '-' . $daysInMonths));
        $monthStartDay = date('N', strtotime($year . '-' . $month . '-01'));
         
        if($monthEndingDay < $monthStartDay) {
            $numOfweeks++;
        }

        return $numOfweeks;
    }
 
    /**
    * calculate number of days in a particular month
    */
    private function daysInMonth($month = null, $year = null)
    {
        if(null == ($year))
            $year =  date("Y", time()); 
         if(null == ($month))
            $month = date("m", time());
             
        return date('t', strtotime($year . '-' . $month . '-01'));
    }

    /**
     *
    */
    public function setEvents($ev)
    {
        $this->events = $ev;
    }

    /**
     * Set day labels in any language
     */
    public function setDayLabels($labels)
    {
        if(!empty($labels) && is_array($labels) && count($labels) == 7) {
            $this->dayLabels = $labels;
        }
    }

        /**
     * Set month labels in any language
     */
    public function setMonthLabels($labels)
    {
        if(!empty($labels) && is_array($labels) && count($labels) <= 31) {
            $this->monthLabels = $labels;
        }
    }
}