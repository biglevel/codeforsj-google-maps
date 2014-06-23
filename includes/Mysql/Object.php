<?php

class Mysql_Object
{

    public function max($column, $length = 12)
    {
        if (isset($this->$column))
        {
            $data = $this->$column;
            if (strlen($data) > $length)
            {
                return substr($data, 0, ($length - 2)) . '..';
            }
            return $data;
        }
        return false;
    }

    public function timestamp($column, $short = false)
    {
        if (isset($this->$column))
        {
            $data = $this->$column;
            if ($data == '0000-00-00 00:00:00' || empty($data))
            {
                return 'N/A';
            }
            return date((($short == false) ? 'M j, Y, \a\t g:ia' : 'm/d/y g:ia'), strtotime($data));
        }
        return false;
    }

    public function enabled($column)
    {
        if (isset($this->$column))
        {
            $data = $this->$column;
            return ($data) ? 'Enabled' : 'Disabled';
        }
        return 'Unknown';
    }

    public function installed($column)
    {
        if (isset($this->$column))
        {
            $data = $this->$column;
            return ($data) ? 'Installed' : 'N/A';
        }
        return 'Unknown';
    }

    public function available($column)
    {
        if (isset($this->$column))
        {
            $data = $this->$column;
            return (!empty($data)) ? $data : 'N/A';
        }
        return 'N/A';
    }

    public function jqueryDay($column)
    {
        if (isset($this->$column))
        {
            $data = $this->$column;
            return date('m/d/Y', strtotime($data));
        }
        return false;
    }

    public function active($column)
    {
        if (isset($this->$column))
        {
            $data = $this->$column;
            return ($data) ? 'Active' : 'Inactive';
        }
        return 'Unknown';
    }

    public function all($column)
    {
        if (isset($this->$column))
        {
            $data = $this->$column;
            return ($data == 0) ? 'All' : $data;
        }
        return 'N/A';
    }

    public function yesno($column)
    {
        if (isset($this->$column))
        {
            $data = $this->$column;
            return ($data) ? 'Yes' : 'No';
        }
        return 'Unknown';
    }

    public function date($column, $include_year = true)
    {
        if (isset($this->$column))
        {
            $data = $this->$column;
            if ($data != '0000-00-00' && $data != '0000-00-00 00:00:00')
            {
                return date(($include_year == true) ? 'M j, Y' : 'M j', strtotime($data));
            }
        }
        return 'N/A';
    }

    public function weekday($column)
    {
        if (isset($this->$column))
        {
            $data = $this->$column;
            if ($data != '0000-00-00' && $data != '0000-00-00 00:00:00')
            {
                return date('l', strtotime($data));
            }
        }
        return 'N/A';
    }

    public function convertSize($column)
    {
        if (isset($this->$column))
        {
            $size = $this->$column;
            if ($size > 0)
            {
                $unit = array('b', 'kb', 'mb', 'gb', 'tb', 'pb');
                return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . $unit[$i];
            }
            return 0;
        }
        return false;
    }

    function friendlyUntil($column, $second_column = false)
    {
        if (isset($this->$column))
        {
            $date = $this->$column;
            if (empty($date))
            {
                return "No date provided";
            }

            $periods = array("sec", "min", "hr", "day", "week", "month", "year", "decade");
            $lengths = array("60", "60", "24", "7", "4.35", "12", "10");

            if ($second_column != false && isset($this->$second_column))
            {
                $now = strtotime($this->$second_column);
            }
            else
            {
                $now = time();
            }
            $unix_date = strtotime($date);

            // check validity of date
            if (empty($unix_date))
            {
                return "N/A";
            }

            // is it future date or past date
            if ($now > $unix_date)
            {
                $difference = $now - $unix_date;
                $tense = "ago";
            }
            else
            {
                $difference = $unix_date - $now;
                $tense = "from now";
            }

            for ($j = 0; $difference >= $lengths[$j] && $j < count($lengths) - 1; $j++)
            {
                $difference /= $lengths[$j];
            }

            $difference = round($difference);

            if ($difference != 1)
            {
                $periods[$j].= "s";
            }

            return "$difference $periods[$j] {$tense}";
        }
        return "N/A";
    }

    public function friendlySize($column, $padHours = false)
    {
        if (isset($this->$column))
        {
            $sec = $this->$column;
            if ($sec > 0)
            {
                $unit = array('b', 'kb', 'mb', 'gb', 'tb', 'pb');

                // start with a blank string
                $hms = "";

                // do the hours first: there are 3600 seconds in an hour, so if we divide
                // the total number of seconds by 3600 and throw away the remainder, we're
                // left with the number of hours in those seconds
                $hours = intval(intval($sec) / 3600);

                // add hours to $hms (with a leading 0 if asked for)
                $hms .= ( $padHours) ? str_pad($hours, 2, "0", STR_PAD_LEFT) . ":" : $hours . ":";

                // dividing the total seconds by 60 will give us the number of minutes
                // in total, but we're interested in *minutes past the hour* and to get
                // this, we have to divide by 60 again and then use the remainder
                $minutes = intval(($sec / 60) % 60);

                // add minutes to $hms (with a leading 0 if needed)
                $hms .= str_pad($minutes, 2, "0", STR_PAD_LEFT) . ":";

                // seconds past the minute are found by dividing the total number of seconds
                // by 60 and using the remainder
                $seconds = intval($sec % 60);

                // add seconds to $hms (with a leading 0 if needed)
                $hms .= str_pad($seconds, 2, "0", STR_PAD_LEFT);

                // done!
                return $hms;
            }
            return "N/A";
        }
        return false;
    }

    public function friendlySeconds($column, $padHours = false)
    {
        if (isset($this->$column))
        {
            $sec = $this->$column;
            if ($sec > 0)
            {
                $unit = array('b', 'kb', 'mb', 'gb', 'tb', 'pb');

                // start with a blank string
                $hms = "";

                // do the hours first: there are 3600 seconds in an hour, so if we divide
                // the total number of seconds by 3600 and throw away the remainder, we're
                // left with the number of hours in those seconds
                $hours = intval(intval($sec) / 3600);

                // add hours to $hms (with a leading 0 if asked for)
                $hms .= ( $padHours) ? str_pad($hours, 2, "0", STR_PAD_LEFT) . ":" : $hours . ":";

                // dividing the total seconds by 60 will give us the number of minutes
                // in total, but we're interested in *minutes past the hour* and to get
                // this, we have to divide by 60 again and then use the remainder
                $minutes = intval(($sec / 60) % 60);

                // add minutes to $hms (with a leading 0 if needed)
                $hms .= str_pad($minutes, 2, "0", STR_PAD_LEFT) . ":";

                // seconds past the minute are found by dividing the total number of seconds
                // by 60 and using the remainder
                $seconds = intval($sec % 60);

                // add seconds to $hms (with a leading 0 if needed)
                $hms .= str_pad($seconds, 2, "0", STR_PAD_LEFT);

                // done!
                return $hms;
            }
            return "N/A";
        }
        return false;
    }

}