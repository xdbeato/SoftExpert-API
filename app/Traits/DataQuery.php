<?php

namespace App\Traits;
use DB;
/**
 *
 */
trait DataQuery
{
    public function select($query)
    {
        $result =  DB::connection('sqlsrv')->select($query);
        return $result;
    }



    public function update($query)
    {
        $result =  DB::connection('sqlsrv')->update($query);
        return $result;
    }

    public function delete($query)
    {
        $result =  DB::connection('sqlsrv')->delete($query);
        return $result;
    }


    public function filterDataFromQueryResult($queryResult, $oldFieldsKeys,$newFieldsKeys)
    {
        $newData = [];
        $queryResult = (array)$queryResult;
        foreach($queryResult as $data)
        {
            $current = [];
            foreach($oldFieldsKeys as $key =>$filter)
            {
                $filter = strtoupper($filter);
                $data = (array)$data;
                $filtered = $data[$filter];
                $newCurrent = [$newFieldsKeys[$key] => $filtered];

                array_push($current, $newCurrent);
            }
            array_push($newData, $current);
        }
        return $newData;
    }

    public function decodeItemsForGrid($items)
    {
        $finalItems = [];
        foreach($items as $item )
        {
            $currentItem = [];
            foreach($item as $value)
            {

                $isDate = preg_match("/fecha/i",key($value));
                if($isDate == 1){
                    $timestamp = strtotime($value[key($value)]);
                    $itemValue =  date("Y-m-d", $timestamp);
                  /*   $array = explode(' ', $itemValue);
                    $itemValue = $array[0];
                    dd('entro con la u'); */
                }
                else
                {
                    $itemValue = $value[key($value)];
                }

                $current = [
                    'key' => key($value),
                    'value' => $itemValue
                ];
                array_push($currentItem, $current);
            }
            array_push($finalItems,$currentItem);
        }
        return $finalItems;

    }

}
