<?php


namespace App\Traits;


trait TextHandler
{
    public function Strong($text)
    {
        return "<strong>$text</strong>";
    }

    function makeBoldText($orimessage)
    {
        $styles = array ( '*' => 'strong', '_' => 'i', '~' => 'strike');
        return preg_replace_callback('/(?<!\w)([*~_])(.+?)\1(?!\w)/',
            function($m) use($styles) {
                return '<'. $styles[$m[1]]. '>'. $m[2]. '</'. $styles[$m[1]]. '>';
            },
            $orimessage);
    }

    public function parseString($cadena)
    {
        $words = explode(' ', $cadena);
        foreach($words as $key => $word)
        {
            $start = substr($word,0,1);
            $end = substr($word,-1);
            if($start == '*' & $end =='*')
            {
                $newWord = str_replace('*','',$word);
                $words[$key] = $this->Strong($newWord);
            }
        }
        return implode(' ',$words);
    }

    public function BuildMessageForPortal($history)
    {
        $all_messages = explode('====================',$history);
        $separated_messages = [];
        foreach ($all_messages as $key => $message)
        {
            $origin = substr($message,0,1);
            $msg = substr($message,1);
            if($origin == "S")
            {
                $msg = ['message' => utf8_encode($msg),  'citizen' => 0];
                array_push($separated_messages,$msg);
            }
            if($origin == "P")
            {
                $msg = ['message' =>utf8_encode($msg),  'citizen' => 1];
                array_push($separated_messages,$msg);
            }
        }
        return $separated_messages;
    }
}
