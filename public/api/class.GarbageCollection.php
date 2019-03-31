<?php

class GarbageCollection
{
    protected $collectBioPeriod = 7;
    protected $collectPlasticPeriod = 14;
    protected $collectRestPeriod = 21;

    protected $knownDateBio = '2019-03-08';
    protected $knownDatePlastic = '2019-03-13';
    protected $knownDateRest = '2019-03-06';

    public function __construct()
    {

    }

    /**
     * Get Bio garbage collection dates for given month
     */
    public function getDatesBio($month = null)
    {
        if($month == null) {
            $month = date("n");
        }

        




    }
}