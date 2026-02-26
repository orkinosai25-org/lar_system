<?php
/**
 * Library which has generic functions to get data
 *
 * @package    Provab Application
 * @subpackage Flight Model
 * @author     Arjun J<arjunjgowda260389@gmail.com>
 * @version    V2
 */
abstract class Transaction extends CI_Model
{
    /**
     * Lock all the tables necessary for flight transaction to be processed
     * 
     * @return void
     */
    public abstract function lock_tables(): void;

    /**
     * Unlock all the tables
     * 
     * @return void
     */
    public static function release_locked_tables(): void
    {
        $CI = &get_instance();
        $CI->db->query('UNLOCK TABLES');
    }
}
