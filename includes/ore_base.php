<?php
/*
 * base class for all ORE classes with utility functions
 */

abstract class OREBase {

    // construct JSON responses to AJAX queries
    public function ajax_response($a) {
        echo json_encode($a);
        die();
    }

    // Debugging related //////////////////////////
    //
    // log things to the web server log
    public function log($message) {
        if (ORE_DEBUG) {
            error_log('+++++ DEBUG('.$this->get_caller_info().'): '.$message);
        }
    }

    public function get_caller_info() {
        $c = '';
        $file = '';
        $func = '';
        $class = '';
        $trace = debug_backtrace();
        if (isset($trace[2])) {
            $file = $trace[1]['file'];
            $line = $trace[1]['line'];
            $func = $trace[2]['function'];
            if ((substr($func, 0, 7) == 'include') || (substr($func, 0, 7) == 'require')) {
                $func = '';
            }
        } else if (isset($trace[1])) {
            $file = $trace[1]['file'];
            $func = '';
        }
        if (isset($trace[3]['class'])) {
            $class = $trace[3]['class'];
            $func = $trace[3]['function'];
            $file = $trace[2]['file'];
            $line = $trace[2]['line'];
        } else if (isset($trace[2]['class'])) {
            $class = $trace[2]['class'];
            $func = $trace[2]['function'];
            $file = $trace[1]['file'];
            $line = $trace[1]['line'];
        }
        if ($file != '') $file = basename($file);
        $c = $file . "(".$line."): ";
        $c .= ($class != '') ? " " . $class . "->" : "";
        $c .= ($func != '') ? $func . "(): " : "";
        return($c);
    }
}

?>
